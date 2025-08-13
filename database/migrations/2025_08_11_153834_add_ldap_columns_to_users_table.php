<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
            $table->string('domain')->nullable()->after('username');
            $table->string('guid')->nullable()->unique()->after('domain');
            $table->string('first_name')->nullable()->after('guid');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('department')->nullable()->after('last_name');
            $table->string('title')->nullable()->after('department');
            $table->string('phone')->nullable()->after('title');
            
            // Make email nullable since LDAP users might not have email
            $table->string('email')->nullable()->change();
            
            // Make password nullable since LDAP users authenticate via LDAP
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'domain',
                'guid',
                'first_name',
                'last_name',
                'department',
                'title',
                'phone',
            ]);
            
            // Restore email and password as required
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
