<?php

namespace App\Modules\Sistema\Manutencao;

use App\Core\Controller\ControllerCore;
use App\Libs\Template\TemplateAbstract;

class ManutencaoController extends ControllerCore
{
    public function index($args = [])
    {
      try {
            $this->isLogged();
            $data = $this->getServico();
            $data["HEAD"]["title"] = "Logs do Sistema";

            $logsDir = dirname(__DIR__, 4) . '/logs/';

            // Detectar todos os arquivos .log, excluindo arquivos de rotação e PID
            $arquivos = glob($logsDir . '*.log') ?: [];
            $canais = [];

            foreach ($arquivos as $arquivo) {
                $nome = basename($arquivo, '.log');

                // Excluir arquivos de controle (.rotation_* e .pid)
                if (str_starts_with($nome, '.')) {
                    continue;
                }

                // Detectar formato com data: canal_YYYY-MM-DD
                if (preg_match('/^(.+)_(\d{4}-\d{2}-\d{2})$/', $nome, $m)) {
                    $canais[$m[1]] = true;
                } else {
                    // Arquivo sem data (ex: campanha_worker)
                    $canais[$nome] = true;
                }
            }

            $canais = array_keys($canais);
            sort($canais);

            // Filtros da requisição
            $canal          = $this->getParams('canal') ?: 'app';
            $dataSelecionada = $this->getParams('data') ?: date('Y-m-d');
            $nivel          = $this->getParams('nivel') ?: '';

            // Detectar datas disponíveis para o canal selecionado
            $datas = [];
            foreach ($arquivos as $arquivo) {
                $nome = basename($arquivo, '.log');
                if (preg_match('/^' . preg_quote($canal, '/') . '_(\d{4}-\d{2}-\d{2})$/', $nome, $m)) {
                    $datas[] = $m[1];
                }
            }
            rsort($datas);

            // Determinar o arquivo a ler
            $arquivoLog = $logsDir . $canal . '_' . $dataSelecionada . '.log';

            // Fallback para arquivo sem data (ex: campanha_worker.log)
            if (!file_exists($arquivoLog)) {
                $arquivoLog = $logsDir . $canal . '.log';
            }

            $linhas = [];
            $resumo = ['ERROR' => 0, 'WARNING' => 0, 'INFO' => 0];

            if (file_exists($arquivoLog)) {
                $todasLinhas = file($arquivoLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                $todasLinhas = array_slice($todasLinhas, -500);

                foreach ($todasLinhas as $linha) {
                    if (!preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \[(\w+)\] (.+)$/', $linha, $m)) {
                        continue;
                    }

                    $timestamp  = $m[1];
                    $nivelLinha = $m[2];
                    $resto      = $m[3];

                    // Aplicar filtro de nível
                    if (!empty($nivel) && $nivelLinha !== $nivel) {
                        continue;
                    }

                    // Separar partes por ' | '
                    $partes    = explode(' | ', $resto);
                    $mensagem  = $partes[0] ?? '';
                    $contexto  = null;
                    $urlLinha  = '';
                    $userLinha = '';

                    $methodLinha = '';
                    $ipLinha     = '';
                    $uaLinha     = '';

                    foreach (array_slice($partes, 1) as $parte) {
                        if (str_starts_with($parte, '{') || str_starts_with($parte, '[')) {
                            $contexto = $parte;
                        } elseif (preg_match('/^(GET|POST|PUT|DELETE|PATCH|CLI) /', $parte, $mm)) {
                            // Formato: "POST /api/rota"
                            [$methodLinha, $urlLinha] = explode(' ', $parte, 2);
                        } elseif (str_starts_with($parte, 'URL: ')) {
                            // Compatibilidade com logs antigos
                            $urlLinha = substr($parte, 5);
                        } elseif (str_starts_with($parte, 'IP: ')) {
                            $ipLinha = substr($parte, 4);
                        } elseif (str_starts_with($parte, 'UA: ')) {
                            $uaLinha = substr($parte, 4);
                        } elseif (str_starts_with($parte, 'User: ')) {
                            $userLinha = substr($parte, 6);
                        }
                    }

                    $resumo[$nivelLinha] = ($resumo[$nivelLinha] ?? 0) + 1;

                    $linhas[] = [
                        'timestamp' => $timestamp,
                        'nivel'     => $nivelLinha,
                        'mensagem'  => $mensagem,
                        'contexto'  => $contexto,
                        'method'    => $methodLinha,
                        'url'       => $urlLinha,
                        'ip'        => $ipLinha,
                        'ua'        => $uaLinha,
                        'user'      => $userLinha,
                    ];
                }

                // Mais recentes primeiro
                $linhas = array_reverse($linhas);
            }

            $data['CANAIS']            = $canais;
            $data['DATAS']             = $datas;
            $data['CANAL_SELECIONADO'] = $canal;
            $data['DATA_SELECIONADA']  = $dataSelecionada;
            $data['NIVEL_SELECIONADO'] = $nivel;
            $data['LINHAS']            = $linhas;
            $data['RESUMO']            = $resumo;

            return $this->render(TemplateAbstract::LOGGED, 'sistema/manutencao/logs', $data);
        } catch (\Error $e) {
            return $e;
        }
    }

 

    public function limparlogs($args = [])
    {
        try {
            $this->isLogged();

            $canal = $this->postParams('canal') ?: '';
            $data  = $this->postParams('data')  ?: '';

            if (empty($canal)) {
                echo json_encode(['error' => true, 'message' => 'Canal não informado.']);
                exit;
            }

            $logsDir    = dirname(__DIR__, 4) . '/logs/';
            $arquivoLog = $logsDir . $canal . '_' . $data . '.log';

            if (!file_exists($arquivoLog)) {
                $arquivoLog = $logsDir . $canal . '.log';
            }

            if (!file_exists($arquivoLog)) {
                echo json_encode(['error' => true, 'message' => 'Arquivo de log não encontrado.']);
                exit;
            }

            file_put_contents($arquivoLog, '');

            echo json_encode(['error' => false, 'message' => 'Log limpo com sucesso.']);
            exit;
        } catch (\Throwable $e) {
            echo json_encode(['error' => true, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
