<?php

namespace App\Libs;

use App\Libs\Tcpdf\TcpdfLib;

class FileLib{

    /**
     * FUNÇÃO ENVIAR ARQUIVOS DIVERSOS
     *
     * @param $file
     * @param $destino
     * @return string
     */
    public function uploadFile($file, $destino)
    {

        // Pega extensão da imagem
        $nomeArquivo = $this->generateFileName($file['name']);

        // Caminho de onde ficará a imagem
        $caminhoArquivo = $destino . "" . $nomeArquivo;
        $caminhoArquivo = $_SERVER['DOCUMENT_ROOT'] . $caminhoArquivo;


        // Faz o upload da imagem para seu respectivo caminho
        move_uploaded_file($file["tmp_name"], $caminhoArquivo);

        return $nomeArquivo;
    }

    public function renameFile($from, $to)
    {
        $from = $_SERVER['DOCUMENT_ROOT'] . $from;
        $to = $_SERVER['DOCUMENT_ROOT'] . $to;

        rename($from, $to);
    }

    /**
     * FUNÇÃO DE COPIAR UM ARQUIVO
     *
     * @param $arquivo
     * @param $destino
     * @return string
     */
    public function copyFile($arquivo, $destino)
    {

        // Pega extensão da imagem
        $nomeArquivo = $this->generateFileName($arquivo['name']);

        copy($destino . $arquivo['tmp_name'], $destino . $nomeArquivo);

        return $nomeArquivo;
    }

    /**
     * FUNÇÃO PARA REMOVER ARQUIVO
     *
     * @param $caminho
     */
    public function removeFile($caminho)
    {
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $caminho))
            return false;

        unlink($_SERVER['DOCUMENT_ROOT'] . $caminho);
        return true;
    }

    /**
     * FUNÇÃO PARA GERAR NOME DO ARQUIVO NO FORMATO MD5
     *
     * @param string $arquivo
     * @return string
     */
    public function generateFileName($arquivo = 'protocolo.pdf')
    {
        $funcoesClass = new FuncoesLib();
        $extensao = $this->getFileExtension($arquivo);
        $nomeArquivo = md5(uniqid(time()) . $funcoesClass->pegaIpUsuario()) . "." . $extensao;

        return $nomeArquivo;
    }

    function generateFileNameBase64($base64)
    {
        $funcoesClass = new FuncoesLib();

        // Tenta extrair o tipo MIME do Base64
        preg_match('/^data:(.*?);base64,/', $base64, $matches);

        if (!empty($matches[1])) {
            // Obtém o tipo MIME (ex: image/jpeg, application/pdf, etc.)
            $mimeType = $matches[1];
            $mimeParts = explode('/', $mimeType);  // Divide em "type/subtype" (ex: image/jpeg)

            if (isset($mimeParts[1])) {
                // Retorna a extensão como o tipo MIME (ex: jpg, pdf, png)
                $extension = $mimeParts[1];
                return md5(uniqid(time()) . $funcoesClass->pegaIpUsuario()) . "." . $extension;

            }
        }

        return null;
    }

    /**
     * FUNÇÃO PARA PEGAR EXTENSÃO DE UM ARQUIVO
     *
     * @param $arquivo
     * @return mixed
     */
    public function getFileExtension($arquivo)
    {

        // Pega extensão da imagem
        return pathinfo($arquivo, PATHINFO_EXTENSION);

    }

    /**
     * FUNÇÃO PARA CONVERTER UMA IMAGEM EM PDF
     *
     * @param $file
     * @param $destino
     * @param $tcpdf
     * @return string
     */
    function convertImgToPdf($file, $destino)
    {
        $fileImage = $this->uploadImage($file, $destino, $file["name"]);
        $caminhoImagem = $_SERVER['DOCUMENT_ROOT'] . $destino . $fileImage;
        $tcpdf = new TcpdfLib();

        $namePdf = $this->generateFileName();
        $caminhoPdf= $_SERVER['DOCUMENT_ROOT'].$destino.$namePdf;
        $tcpdf->imageToPdf($caminhoImagem, $caminhoPdf);
        $this->removeFile($caminhoImagem);

        return $namePdf;
    }

    /**
     * FUNÇÃO ENVIAR IMAGEM
     *
     * @param $destinoFoto
     * @param $file
     * @param $fotoAtual
     *
     * @return string
     */
    function uploadImage($file, $destinoFoto, $fotoAtual = "default.png", $imgDefault = "default.png")
    {


        // Se não houver nenhum erro

        // Pega extensão da imagem
        preg_match("/\.(gif|bmp|png|jpg|jpeg){1}$/i", $file["name"], $ext);

        // Gera um nome único para a imagem
        $nome_imagem = md5(uniqid(time())) . "." . $ext[1];

        // Caminho de onde ficará a imagem
        $toImage = $destinoFoto . "" . $nome_imagem;
        $toImage = $_SERVER['DOCUMENT_ROOT'] . $toImage;

        //$fromImage = $file["tmp_name"];
        $fromImage = $this->resizeImage($file["tmp_name"], $ext[1])??$file["tmp_name"];
        // Faz o upload da imagem para seu respectivo caminho
        move_uploaded_file($fromImage, $toImage);
        //move_uploaded_file($foto["tmp_name"], $caminho_imagem);


        //REMOVE IMAGEM ANTIGA
        if ($fotoAtual <> $imgDefault && !empty($fotoAtual)) {
            $this->removeFile($destinoFoto . $fotoAtual);
        }

        return $nome_imagem;
    }

    function uploadFromUrl($url, $destinoFoto, $fileDefault = "default.png")
    {
        // Se não houver nenhum erro
        if (empty($url)) {
            return $fileDefault; // Retorna imagem padrão se a URL estiver vazia
        }

        // Verifica se a URL é válida
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $fileDefault; // Retorna imagem padrão se a URL for inválida
        }

        // Baixa o conteúdo da imagem
        $imageData = file_get_contents($url);
        if ($imageData === false) {
            return $fileDefault; // Falha ao baixar a imagem
        }

        // Verifica se é uma imagem válida usando os dados baixados
        $imageInfo = getimagesizefromstring($imageData);
        if ($imageInfo !== false) {
            $extension = image_type_to_extension($imageInfo[2], false);
            $fileName = md5(uniqid(time())) . '.' . $extension;

            // Caminho de onde ficará a imagem (corrigido)
            $toImage = $destinoFoto . $fileName;
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . $toImage;

            // Salva a imagem
            if (file_put_contents($uploadPath, $imageData)) {
                return $fileName;
            }
        }

        return $fileDefault; // Retorna uma imagem padrão se algo falhar
    }

    /**
     * REDIMENSIONAR IMAGENS
     *
     * @param $caminho_imagem
     * @param $extensao
     * @return string
     */
    public function resizeImage($caminho_imagem, $extensao)
    {
        try {
            // Retorna o identificador da imagem
            if ($extensao == 'jpeg' || $extensao == 'jpg' || $extensao == 'JPG' || $extensao == 'JPEG')
                $imagem = @imagecreatefromjpeg($caminho_imagem);
            else if ($extensao == 'png' || $extensao == 'PNG')
                $imagem = @imagecreatefrompng($caminho_imagem);
            else if ($extensao == 'gif' || $extensao == 'GIF')
                $imagem = @imagecreatefromgif($caminho_imagem);

            if (!function_exists('exif_read_data'))
                return $caminho_imagem;

            // PEGA VALORES PARA ROTAÇÃO
            $exif = @exif_read_data($caminho_imagem,0, true);
            $angulo = 0;
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 8:
                        $angulo = 90;
                        break;
                    case 3:
                        $angulo = 180;
                        break;
                    case 6:
                        $angulo = -90;
                        break;
                    default:
                        $angulo = 0;
                }
            }

            // Cria duas variáveis com a largura e altura da imagem
            list($largura, $altura) = @getimagesize($caminho_imagem);

            // Nova largura e altura
            $proporcao = 1080;
            $nova_largura = $proporcao;
            $nova_altura = (int)(($altura * $proporcao) / $largura);

            // Cria uma nova imagem em branco
            $nova_imagem = @imagecreatetruecolor($nova_largura, $nova_altura);
            @imagesavealpha($nova_imagem, true);
            $cor_fundo = @imagecolorallocatealpha($nova_imagem, 0, 0, 0, 127);
            @imagefill($nova_imagem, 0, 0, $cor_fundo);


            // Copia a imagem para a nova imagem com o novo tamanho
            @imagecopyresampled(
                $nova_imagem, // Nova imagem
                $imagem, // Imagem original
                0, // Coordenada X da nova imagem
                0, // Coordenada Y da nova imagem
                0, // Coordenada X da imagem
                0, // Coordenada Y da imagem
                $nova_largura, // Nova largura
                $nova_altura, // Nova altura
                $largura, // Largura original
                $altura // Altura original
            );

            $imgRotation = imagerotate($nova_imagem, $angulo, 0);

            // Cria a imagem
            if ($extensao == 'jpeg' || $extensao == 'jpg' || $extensao == 'JPG' || $extensao == 'JPEG')
                @imagejpeg($imgRotation, $caminho_imagem, 40);
            else if ($extensao == 'png' || $extensao == 'PNG')
                @imagepng($imgRotation, $caminho_imagem, 4);
            else if ($extensao == 'gif' || $extensao == 'GIF')
                @imagegif($imgRotation, $caminho_imagem);


            // Remove as imagens temporárias
            @imagedestroy($imagem);
            @imagedestroy($nova_imagem);
            @imagedestroy($imgRotation);

            return $caminho_imagem;
        } catch (\ErrorException $e) {
            return null;
        }
    }

    /**
     * @param $file
     * @return bool
     */
    public static function isImage($file)
    {
        if (empty($file["tmp_name"]))
            return false;
        if (!preg_match('/^image\/(pjpeg|jpeg|png|gif|bmp|jpg)$/', $file["type"]))
            return false;

        return true;
    }

    /**
     * @param $file
     * @return bool
     */
    public static function isPdf($file)
    {
        if (empty($file["tmp_name"]))
            return false;

        return (in_array($file['type'], ['application/pdf']));
    }

    public static function isEmpty($file)
    {
        if (!empty($file["tmp_name"]))
            return false;

        return true;
    }

    public function uploadFileBase64(array|string|null $string, string $to)
    {
        $caminhoImagem = $_SERVER['DOCUMENT_ROOT'] . $to;

        $name = $this->generateFileNameBase64($string);

        $base64Image = $string;
        $imageData = explode(',', $base64Image)[1];
        $imageData = base64_decode($imageData);
        file_put_contents($caminhoImagem.$name, $imageData);

        return $name;
    }
}