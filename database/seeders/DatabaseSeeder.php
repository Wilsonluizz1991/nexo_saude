<?php

namespace Database\Seeders;

use App\Models\Alerta;
use App\Models\Indicacao;
use App\Models\Operadora;
use App\Models\Tarefa;
use App\Models\User;
use App\Services\AssinaturaService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            TipoDocumentoSeeder::class,
            RequisitoDocumentalSeeder::class,
        ]);

        foreach ([
            'Bradesco Saúde', 
            'SulAmérica Saúde', 
            'Amil', 
            'Unimed', 
            'NotreDame Intermédica', 
            'Porto Saúde', 
            'Alice', 
            'Care Plus',
            'Hapvida',
            'Unimed Nacional',
            'Seguros Unimed',
            'Prevent Senior',
            'Golden Cross',
            'Medsênior',
            'Omint',
            'Sami Saúde',
            'Allianz Saúde',
            'Blue Med Saúde',
            'Unimed BH',
            'Sompo Saúde',
            'Plena Saúde'
        ] as $nome) {
            Operadora::firstOrCreate(['nome' => $nome], ['ativa' => true]);
        }

        $corretor = User::firstOrCreate(['email' => 'carlos@nexosaude.local'], [
            'name' => 'Carlos Oliveira',
            'telefone' => '(11) 99999-0000',
            'perfil' => 'Corretor',
            'password' => Hash::make('password'),
        ]);

        if (! $corretor->assinatura) {
            app(AssinaturaService::class)->iniciarTesteGratis($corretor);
        }

        $corretor->corretorPerfil()->updateOrCreate(['user_id' => $corretor->id], [
            'slug' => 'carlos-oliveira',
            'nome_publico' => 'Carlos Oliveira',
            'bio' => 'Corretor especializado em planos de saúde para famílias, profissionais liberais e pequenas empresas.',
            'especialidades' => ['Planos familiares', 'PME', 'Adesão'],
            'cidade_regiao' => 'São Paulo e Grande São Paulo',
        ]);

        $indicacao = Indicacao::firstOrCreate([
            'user_id' => $corretor->id,
            'email' => 'ana@example.com',
        ], [
            'origem' => 'link_publico',
            'nome_cliente' => 'Ana Martins',
            'telefone' => '(11) 98888-1111',
            'tipo_plano' => 'Plano familiar',
            'quantidade_vidas' => 3,
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'possui_preferencias' => true,
            'operadoras_preferidas' => [1, 2],
            'hospitais_preferidos' => ['Hospital São Luiz', 'Hospital Albert Einstein'],
            'faixa_valor_mensal' => 'R$ 1.500 a R$ 2.500',
            'etapa' => 'lead',
            'status' => 'nova',
        ]);
        $indicacao->timelineEventos()->firstOrCreate(['titulo' => 'Lead criado'], ['descricao' => 'Pedido recebido pelo link público.']);

        Tarefa::firstOrCreate(['user_id' => $corretor->id, 'titulo' => 'Retornar para Ana Martins'], [
            'indicacao_id' => $indicacao->id,
            'vencimento' => now()->addDay()->toDateString(),
            'status' => 'pendente',
        ]);

        Alerta::firstOrCreate(['user_id' => $corretor->id, 'titulo' => 'Teste grátis ativo'], [
            'mensagem' => 'Sua conta está no período de 30 dias grátis.',
            'tipo' => 'info',
            'lido' => false,
        ]);
    }
}
