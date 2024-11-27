<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddServantCardReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999603], [
            'parent_id' => Menu::query()->where('old', 999916)->firstOrFail()->getKey(),
            'process' => 999603,
            'title' => 'Carteira de Servidor',
            'order' => 0,
            'old' => 999822,
            'link' => '/module/Reports/ServantCard'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999603)->delete();
    }
}
