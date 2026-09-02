<?php

use App\Models\LegacyInstitution;

class ClassBoardMapController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999609;

    protected $_titulo = 'Relatório Mapa do Conselho de Classe';

    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Emissão do mapa do conselho de classe', [
            url('educar_index.php') => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);
        $this->inputsHelper()->dynamic(['etapa'], ['required' => false]);

        if (config('legacy.app.matricula.dependencia') == 1) {
            $options = ['label' => 'Alunos com dependência',
                'resources' => [0 => 'Todos',
                    1 => 'Somente alunos com dependência',
                    2 => 'Não exibir alunos com dependência'],
                'required' => false,
                'value' => 0];
            $this->inputsHelper()->select('dependencia', $options);
        } else {
            $this->inputsHelper()->hidden('dependencia', ['value' => 0]);
        }

        $this->inputsHelper()->dynamic('situacaoMatricula');

        $this->inputsHelper()->select('formato', [
            'label' => 'Formato',
            'resources' => ['pdf' => 'PDF', 'csv' => 'CSV'],
            'required' => false,
            'value' => 'pdf',
        ]);

        $options = ['label' => 'Orientação',
            'resources' => ['paisagem' => 'Paisagem',
                'retrato' => 'Retrato'],
            'required' => false,
            'value' => 1];

        $this->inputsHelper()->select('orientacao', $options);

        $this->inputsHelper()->checkbox('emitir_assinaturas', ['label' => 'Emitir assinaturas abaixo do mapa?']);

        //Carrega javascript
        $this->loadResourceAssets($this->getDispatcher());
    }

    public function report()
    {
        return new ClassBoardMapReport();
    }

    public function beforeValidation()
    {
        $institution = app(LegacyInstitution::class);
        $this->report->addArg('order_sequential', (int) $institution->ordenar_alunos_sequencial_enturmacao ?: 0);
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('etapa', (int) $this->getRequest()->etapa);
        $this->report->addArg('dependencia', (int) $this->getRequest()->dependencia);
        $this->report->addArg('emitir_assinaturas', (bool) $this->getRequest()->emitir_assinaturas);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao_matricula_id);
        $this->report->addArg('orientacao', (string) $this->getRequest()->orientacao);
        $this->report->addArg('formato', (string) $this->getRequest()->formato);
    }

    public function renderReport()
    {
        if (($this->report->args['formato'] ?? 'pdf') !== 'csv') {
            parent::renderReport();

            return;
        }

        try {
            $data = $this->report->getJsonData();
            $data = $this->report->modify($data);
            $rows = $data['main'] ?? [];

            if (empty($rows)) {
                $this->renderError('Nenhum dado encontrado para os filtros selecionados.');

                return;
            }

            $csv = $this->buildCsv($rows);

            $nomeTurma = $rows[0]['nome_turma'] ?? 'mapa';

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="mapa-conselho-' . $nomeTurma . '.csv"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . strlen($csv));

            ob_clean();
            flush();

            echo $csv;
            exit();
        } catch (Exception $e) {
            $this->renderError('Erro ao gerar CSV: ' . $e->getMessage());
        }
    }

    private function buildCsv(array $rows): string
    {
        $etapa = (int) ($this->report->args['etapa'] ?? 0);
        $nomeTurma = $rows[0]['nome_turma'] ?? '';

        $disciplinas = [];
        foreach ($rows as $row) {
            $key = $row['componente_order'] . '_' . $row['nm_componente_curricular'];
            if (!isset($disciplinas[$key])) {
                $disciplinas[$key] = $row['nm_componente_curricular'];
            }
        }
        ksort($disciplinas);
        $disciplinasList = array_values($disciplinas);

        $alunos = [];
        foreach ($rows as $row) {
            $alunoKey = $row['cod_aluno'] . '_' . $row['matricula'];

            if (!isset($alunos[$alunoKey])) {
                $alunos[$alunoKey] = [
                    'nome' => $row['nm_aluno'],
                    'sequencial_fechamento' => $row['sequencial_fechamento'],
                    'notas' => [],
                ];
            }

            $nota = $this->resolveNota($row, $etapa);
            $alunos[$alunoKey]['notas'][$row['nm_componente_curricular']] = $nota;
        }

        $lines = [];

        // BOM UTF-8
        $lines[] = "\xEF\xBB\xBF";

        // Cabeçalho da turma
        $lines[] = 'Turma ' . $nomeTurma;

        // Cabeçalho das colunas
        $lines[] = 'Nome do Aluno;' . implode(';', $disciplinasList);

        // Dados dos alunos
        foreach ($alunos as $aluno) {
            $notas = [];
            foreach ($disciplinasList as $disciplina) {
                $notas[] = $aluno['notas'][$disciplina] ?? '';
            }
            $lines[] = $aluno['nome'] . ';' . implode(';', $notas);
        }

        // Separador final
        $lines[] = str_repeat('-', 80);

        return implode("\r\n", $lines);
    }

    private function resolveNota(array $row, int $etapa): string
    {
        if ($etapa > 0 && $etapa <= 4) {
            return (string) ($row['nota' . $etapa] ?? '');
        }

        // Etapa 0 (todas): média das notas disponíveis
        $notas = [];
        for ($i = 1; $i <= 4; $i++) {
            $val = $row['nota' . $i] ?? null;
            if ($val !== null && $val !== '') {
                $numeric = str_replace(',', '.', (string) $val);
                if (is_numeric($numeric)) {
                    $notas[] = (float) $numeric;
                }
            }
        }

        if (empty($notas)) {
            return '';
        }

        $media = array_sum($notas) / count($notas);

        return str_replace('.', ',', number_format($media, 1));
    }
}
