<?php

use App\Models\LegacyInstitution;

class ServantCardController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999602;

    /**
     * @inheritdoc
     */
    protected $_titulo = 'Carteira de Servidor';
    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Emissão de carteira de Servidor', [
            url('educar_servidores_index.php') => 'Servidores',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola']);
        $this->inputsHelper()->checkbox('branco', ['label' => 'Emitir em branco?', 'required' => false]);
        $this->inputsHelper()->simpleSearchServidor('servidor', ['label' => 'Servidor', 'required' => false]);
        $this->loadResourceAssets($this->getDispatcher());
    }
    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $branco = (bool) $this->getRequest()->branco;

        if (!$branco) {
            $this->report->addArg('servidor', (int) $this->getRequest()->servidor_id);
        }

        $this->report->addArg('branco', $branco);
    }
    /**
     * @return ServantCardReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new ServantCardReport();
    }
}
