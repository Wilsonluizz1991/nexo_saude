<?php

namespace App\Services;

use App\Models\Indicacao;
use App\Models\User;

class WhatsAppLinkService
{
    public const DEFAULT_LEAD_TEMPLATE = 'Olá, {nome}! Tudo bem? Sou seu corretor da Nexo Saúde. Recebi seu interesse em um plano {tipo_plano} para {quantidade_vidas} vida(s) e gostaria de te ajudar com as melhores opções.';

    public function sanitizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        return str_starts_with($digits, '55') ? $digits : '55'.$digits;
    }

    public function buildClientLink(?string $phone): string
    {
        $phone = $this->sanitizePhone((string) $phone);

        return $phone !== '' ? 'https://wa.me/'.$phone : '#';
    }

    public function buildLeadLink(Indicacao $lead, User $corretor): string
    {
        $phone = $this->sanitizePhone((string) $lead->telefone);

        if ($phone === '') {
            return '#';
        }

        $template = $corretor->corretorPerfil?->mensagem_primeiro_contato_whatsapp
            ?: self::DEFAULT_LEAD_TEMPLATE;

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($this->parseMessageTemplate($template, $lead));
    }

    public function parseMessageTemplate(string $template, Indicacao $lead): string
    {
        return strtr($template, [
            '{nome}' => (string) $lead->nome_cliente,
            '{telefone}' => (string) $lead->telefone,
            '{tipo_plano}' => (string) $lead->tipo_plano,
            '{quantidade_vidas}' => (string) $lead->quantidade_vidas,
            '{cidade}' => (string) $lead->cidade,
            '{estado}' => (string) $lead->estado,
        ]);
    }
}
