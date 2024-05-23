<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddStudentCarodromoReportMenu extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Menu::query()->updateOrCreate([
            'old' => 999700,
        ], [
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Carodromo',
            'order' => 4,
            'parent_old' => 21127,
        ]);
        Menu::query()->updateOrCreate(['old' => 999603], [
            'parent_id' => Menu::query()->where('old', 999700)->firstOrFail()->getKey(),
            'process' => 999603,
            'title' => 'Carodromo de Turma',
            'order' => 0,
            'parent_old' => 999700,
            'link' => '/module/Reports/StudentCarodromo'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Menu::query()->where('old', 999603)->delete();
    }
};
