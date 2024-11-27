<?php

use iEducar\Reports\JsonDataSource;

class ServantCardReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'servant-card-model1';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('ano');
    }

    /**
     * @inheritdoc
     */
    public function getJsonData()
    {
        /*
        $queryMainReport = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();

        $servants = Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport);

        return [
            'main' => $servants,
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
        ];*/

        return [
            'main' => (new QueryServantCard())->get($this->args),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        return $this->args['branco'] == 1
            ? $this->getSqlBlankReport()
            : $this->getSqlReport();
    }

    /**
     * Retorna o SQL para o relatório emitido em branco.
     *
     * @return string
     */
    private function getSqlBlankReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;

        return "

            SELECT
                pmieducar.instituicao.nm_instituicao as \"nome_instituicao\",
                cadastro.juridica.fantasia as \"nm_escola\",
                to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
                to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual
            FROM
                pmieducar.instituicao
            INNER JOIN pmieducar.escola ON TRUE
                AND escola.ref_cod_instituicao = instituicao.cod_instituicao
            INNER JOIN cadastro.juridica ON TRUE
                AND juridica.idpes = escola.ref_idpes
            WHERE TRUE
                AND instituicao.cod_instituicao = {$instituicao}
                AND cod_escola = {$escola}
            GROUP BY
                nome_instituicao, nm_escola
            ORDER BY
                nm_escola

        ";
    }

    /**
     * Retorna SQL para o relatório emitido com dados.
     *
     * @return string
     */
    private function getSqlReport()
    {
        $ano = $this->args[''] ?: 0;
        $instituicao = $this->args['ano'] ?: 0;
        $escola = $this->args['instituicao'] ?: 0;
        $servidor = $this->args['servidor'] ?: 0;

        return "

            SELECT DISTINCT
                pmieducar.instituicao.nm_instituicao,
                servidor.cod_servidor,
                translate(upper(municipio_nasceu.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS cidade_nasceu,
                translate(upper(municipio_nasceu.sigla_uf),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS estado_nasceu,
                translate(upper(bairro.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS bairro_servidor,
                translate(upper(pais_nasceu.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS pais_nasceu,
                translate(upper(municipio_mora.sigla_uf),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS estado_casa_servidor,
                translate(upper(municipio_mora.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS municipio_casa_servidor,
                translate(upper(logradouro.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nm_logradouro,
                translate(upper(logradouro.idtlog::varchar),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS tipo_logradouro,
                translate(upper(endereco_pessoa.complemento),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS complemento,
                endereco_pessoa.cep AS cep,
                endereco_pessoa.numero AS numero_casa,
                translate(upper(pessoa.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nm_servidor,
                translate(upper(pessoa.email),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS email,
                translate(upper(estado_civil.descricao),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS estado_civil,
                translate(upper(religions.name),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS religiao,
                translate(upper(documento.sigla_uf_exp_rg),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS sigla_uf_exp_rg,
                translate(upper(documento.sigla_uf_cert_civil),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS sigla_uf_cert_civil,
                translate(upper(orgao_emissor_rg.sigla),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS orgao_exp,
                translate(upper(pessoa_pai.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nome_pai,
                translate(upper(pessoa_mae.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nome_mae,
                fisica.sexo AS sexo,
                to_char(fisica.data_nasc,'dd/mm/yyyy') AS data_nasc,
                lpad(cast(fisica.cpf AS varchar),11,'0') AS cpf,
                fisica.nis_pis_pasep AS nis_pis_pasep,
                documento.rg AS rg,
                to_char(documento.data_exp_rg,'dd/mm/yyyy') AS data_exp_rg,
                documento.num_termo AS num_termo,
                documento.num_livro AS num_livro,
                documento.num_folha AS num_folha,
                to_char(documento.data_emissao_cert_civil,'dd/mm/yyyy') AS data_emissao_cert_civil,
                documento.num_tit_eleitor AS num_tit_eleitor,
                documento.zona_tit_eleitor AS zona_tit_eleitor,
                documento.secao_tit_eleitor AS secao_tit_eleitor,
                documento.cartorio_cert_civil_inep AS cartorio_cert_civil_inep,
                documento.cartorio_cert_civil AS cartorio_cert_civil,
                documento.certidao_nascimento AS certidao_nascimento,
                documento.certidao_casamento AS certidao_casamento,
                documento.tipo_cert_civil AS int_tipo_cert_civil,
                documento.certidao_nascimento AS eh_certidao_nascimento,
                documento.certidao_casamento AS eh_certidao_casamento,
                (
                    CASE WHEN telefone.fone IS NOT NULL
                        AND telefone_add.fone IS NOT NULL
                        AND telefone_fax.fone IS NOT NULL
                    THEN
                        '(' || telefone.ddd || ') ' || telefone.fone || ' - ' || '('|| telefone_add.ddd ||') '|| telefone_add.fone || ' - '|| 'FAX: (' || telefone_fax.ddd ||') '|| telefone_fax.fone ||'.'
                    WHEN telefone.fone IS NOT NULL
                        AND telefone_add.fone IS NULL
                        AND telefone_fax.fone IS NULL
                    THEN '(' || telefone.ddd || ') ' || telefone.fone
                    WHEN telefone.fone IS NULL
                        AND telefone_add.fone IS NOT NULL
                        AND telefone_fax.fone IS NULL
                    THEN
                        '(' || telefone_add.ddd || ') ' || telefone_add.fone
                    WHEN telefone.fone IS NULL
                        AND telefone_add.fone IS NULL
                        AND telefone_fax.fone IS NOT NULL
                    THEN
                        '(' || telefone_fax.ddd || ') ' || telefone_fax.fone
                    WHEN telefone.fone IS NOT NULL
                        AND telefone_add.fone IS NOT NULL
                        AND telefone_fax.fone IS NULL
                    THEN
                        '(' || telefone.ddd || ') ' || telefone.fone || ' - ' || '('|| telefone_add.ddd ||') '|| telefone_add.fone || '.'
                    WHEN telefone.fone IS NOT NULL
                        AND telefone_add.fone IS NULL
                        AND telefone_fax.fone IS NOT NULL
                    THEN
                        '(' || telefone.ddd || ') ' || telefone.fone || ' - ' || 'FAX: (' || telefone_fax.ddd ||') '|| telefone_fax.fone || '.'
                    WHEN telefone.fone IS NULL
                        AND telefone_add.fone IS NOT NULL
                        AND telefone_fax.fone IS NOT NULL
                    THEN
                        '('|| telefone_add.ddd ||') '|| telefone_add.fone || ' - '|| 'FAX: (' || telefone_fax.ddd ||') '|| telefone_fax.fone || '.'
                    ELSE ''
                 END
                ) AS telefones,
                celular.ddd AS celular_ddd,
                celular.fone AS celular_fone,
                fisica_foto.caminho,
                fisica.data_admissao AS dt_adimissao
            FROM
                cadastro.pessoa pessoa
            INNER JOIN cadastro.fisica ON TRUE
                AND pessoa.idpes = fisica.idpes
            INNER JOIN pmieducar.servidor ON TRUE
                AND pessoa.idpes = servidor.cod_servidor
            LEFT JOIN cadastro.estado_civil ON TRUE
                AND estado_civil.ideciv = fisica.ideciv
            LEFT JOIN pmieducar.religions ON TRUE
                AND fisica.ref_cod_religiao = religions.id
            LEFT JOIN cadastro.documento ON TRUE
                AND pessoa.idpes = documento.idpes
            LEFT JOIN cadastro.endereco_pessoa ON TRUE
                AND pessoa.idpes = endereco_pessoa.idpes
            LEFT JOIN public.logradouro ON TRUE
                AND logradouro.idlog = endereco_pessoa.idlog
            LEFT JOIN public.municipio municipio_mora ON TRUE
                AND logradouro.idmun = municipio_mora.idmun
            LEFT JOIN public.bairro ON TRUE
                AND bairro.idbai = endereco_pessoa.idbai
            LEFT JOIN cadastro.orgao_emissor_rg ON TRUE
                AND orgao_emissor_rg.idorg_rg = documento.idorg_exp_rg
            LEFT JOIN cadastro.fone_pessoa telefone ON TRUE
                AND telefone.idpes = pessoa.idpes
                AND telefone.tipo = 1
            LEFT JOIN cadastro.fone_pessoa telefone_add ON TRUE
                AND telefone_add.idpes = pessoa.idpes
                AND telefone_add.tipo = 2
            LEFT JOIN cadastro.fone_pessoa celular ON TRUE
                AND celular.idpes = pessoa.idpes
                AND celular.tipo = 3
            LEFT JOIN cadastro.fone_pessoa telefone_fax ON TRUE
                AND telefone_fax.idpes = pessoa.idpes
                AND telefone_fax.tipo = 4
            LEFT JOIN public.municipio municipio_nasceu ON TRUE
                AND municipio_nasceu.idmun = fisica.idmun_nascimento
            LEFT JOIN public.uf ON TRUE
                AND municipio_nasceu.sigla_uf = uf.sigla_uf
            LEFT JOIN public.pais pais_nasceu ON TRUE
                AND pais_nasceu.idpais = uf.idpais
            LEFT JOIN cadastro.pessoa pessoa_mae ON TRUE
                AND pessoa_mae.idpes = fisica.idpes_mae
            LEFT JOIN cadastro.pessoa pessoa_pai ON TRUE
                AND pessoa_pai.idpes = fisica.idpes_pai
            LEFT JOIN pmieducar.servidor_alocacao ON TRUE
                AND servidor_alocacao.ref_cod_servidor = servidor.cod_servidor
            LEFT JOIN cadastro.fisica_foto ON TRUE
                AND fisica_foto.idpes = pessoa.idpes
            LEFT JOIN pmieducar.instituicao ON 
             (instituicao.cod_instituicao = {$instituicao})
            WHERE TRUE
                AND servidor_alocacao.ano = {$ano}
                AND servidor.ref_cod_instituicao = {$instituicao}
                AND servidor_alocacao.ref_cod_escola = {$escola}
                AND
                (
                    SELECT CASE WHEN {$servidor} = 0 THEN
                        TRUE
                    ELSE
                        servidor.cod_servidor = {$servidor}
                    END
                )
        ";
    }

}
