<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usuarios')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('usuarios', 'oauth_provider')) {
                $table->string('oauth_provider', 50)->nullable()->after('foto_url');
            }

            if (! Schema::hasColumn('usuarios', 'oauth_id')) {
                $table->string('oauth_id', 191)->nullable()->after('oauth_provider');
            }

            if (! Schema::hasColumn('usuarios', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('rol_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('usuarios')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'oauth_id')) {
                $table->dropColumn('oauth_id');
            }

            if (Schema::hasColumn('usuarios', 'oauth_provider')) {
                $table->dropColumn('oauth_provider');
            }
        });
    }
};
