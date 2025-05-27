<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateCategoryToSpenNameInSpendingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spendings', function (Blueprint $table) {
            $table->string('spen_name');
        });

        DB::table('spendings')->update([
            'spen_name' => DB::raw('category')
        ]);

        Schema::table('spendings', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spendings', function (Blueprint $table) {
            $table->string('category');
        });

        DB::table('spendings')->update([
            'category' => DB::raw('spen_name')
        ]);

        Schema::table('spendings', function (Blueprint $table) {
            $table->dropColumn('spen_name');
        });
    }
}
