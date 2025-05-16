<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCategoryIdToSpendingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spendings', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
        });

        // Create default categories from existing spen_name values
        $existingCategories = DB::table('spendings')
            ->select('spen_name')
            ->distinct()
            ->get()
            ->pluck('spen_name');

        foreach ($existingCategories as $categoryName) {
            DB::table('categories')->insert([
                'name' => $categoryName,
                'description' => 'Automatically created from existing spending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Update spendings with corresponding category_id
        foreach ($existingCategories as $categoryName) {
            $category = DB::table('categories')->where('name', $categoryName)->first();
            DB::table('spendings')
                ->where('spen_name', $categoryName)
                ->update(['category_id' => $category->id]);
        }

        Schema::table('spendings', function (Blueprint $table) {
            $table->dropColumn('spen_name');
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
            $table->string('spen_name')->nullable();
        });

        // Restore spen_name values from categories
        $spendings = DB::table('spendings')
            ->select('id', 'category_id')
            ->get();

        foreach ($spendings as $spending) {
            if ($spending->category_id) {
                $category = DB::table('categories')->find($spending->category_id);
                if ($category) {
                    DB::table('spendings')
                        ->where('id', $spending->id)
                        ->update(['spen_name' => $category->name]);
                }
            }
        }

        Schema::table('spendings', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
}
