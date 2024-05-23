<?php

use App\Models\LegacyInstitution;

class StudentCarodromoController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999603;

    protected $_titulo = 'Carodromo Alunos por Turma';

    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Emissão de carodromo de estudante', [
            url('educar_index.php') => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);
        $this->inputsHelper()->dynamic('matricula', ['required' => false]);

        $options = [
            'label' => 'Situação da matrícula',
            'resources' => [
                1 => 'Aprovado',
                2 => 'Reprovado',
                14 => 'Reprovado por falta',
                3 => 'Cursando',
                4 => 'Transferido',
                5 => 'Reclassificado',
                6 => 'Abandono',
                9 => 'Exceto Transferidos/Abandono',
                10 => 'Todas',
                12 => 'Aprovado com dependência',
                16 => 'Aprovado após exame'
            ],
            'required' => false,
            'value' => 9
        ];
        $this->inputsHelper()->select('situacao_matricula', $options);

        if (config('legacy.report.mostrar_relatorios') == 'botucatu') {
            $this->inputsHelper()->hidden('modelo', ['value' => 3]);
        } else {
            $resources = [
                1 => 'Modelo 1',
                2 => 'Modelo 2',
                3 => 'Modelo 3'
            ];

            $options = ['label' => 'Modelo', 'resources' => $resources, 'value' => 1];
            $this->inputsHelper()->select('modelo', $options);
        }
        $this->loadResourceAssets($this->getDispatcher());
    }

    public function report()
    {
        return new StudentCarodromoReport();
    }

    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        
        $configPath = config('legacy.report.caminho_fundo_carteira_transporte');
        $path = empty($configPath) ? '/var/www/ieducar/ieducar/modules/Reports/Assets/Images/StudentCard' : $configPath;

        //$this->report->addArg('caminho_fundo_carteira_transporte', $path);
        if (!isset($_POST['ref_cod_matricula'])) {
            $this->report->addArg('matricula', 0);
        } else {
            $this->report->addArg('matricula', (int) $this->getRequest()->ref_cod_matricula);
        }
        $this->report->addArg('situacao_matricula', $this->getRequest()->situacao_matricula);

        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);
    }
}
