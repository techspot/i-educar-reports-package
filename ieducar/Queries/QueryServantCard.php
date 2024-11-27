<?php

class QueryServantCard extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
            SELECT DISTINCT
                pmieducar.instituicao.nm_instituicao,
                pmieducar.escola.sigla as nm_escola,
                servidor.cod_servidor,
                translate(upper(pessoa.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nm_servidor,
                translate(upper(pessoa.email),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS email,
                lpad(cast(fisica.cpf AS varchar),11,'0') AS cpf,
                fisica_foto.caminho AS foto,
                fisica.data_admissao AS dt_adimissao
            FROM
                cadastro.pessoa pessoa
            INNER JOIN cadastro.fisica ON TRUE
                AND pessoa.idpes = fisica.idpes
            INNER JOIN pmieducar.servidor ON TRUE
                AND pessoa.idpes = servidor.cod_servidor
            LEFT JOIN pmieducar.servidor_alocacao ON TRUE
                AND servidor_alocacao.ref_cod_servidor = servidor.cod_servidor
            LEFT JOIN cadastro.fisica_foto ON TRUE
                AND fisica_foto.idpes = pessoa.idpes 
            LEFT JOIN pmieducar.instituicao ON 
             (instituicao.cod_instituicao = $P{instituicao}) 
            LEFT JOIN pmieducar.escola on (escola.cod_escola = $P{escola})
            WHERE TRUE
                AND servidor_alocacao.ano = $P{ano}
                AND servidor.ref_cod_instituicao = $P{instituicao}
                AND servidor_alocacao.ref_cod_escola = $P{escola}
                AND
                (
                    SELECT CASE WHEN $P{servidor} = 0 THEN
                        TRUE
                    ELSE
                        servidor.cod_servidor = $P{servidor}
                    END
                );
SQL;
    }
}