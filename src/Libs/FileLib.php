<?php

namespace App\Libs;

use App\Libs\Tcpdf\TcpdfLib;

class FileLib
{
    private string $appRoot;

    public function __construct()
    {
        $this->appRoot = dirname(__DIR__, 2);
    }

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
        $caminhoArquivo = $this->appRoot . $caminhoArquivo;


        // Faz o upload da imagem para seu respectivo caminho
        move_uploaded_file($file["tmp_name"], $caminhoArquivo);

        return $nomeArquivo;
    }

    public function renameFile($from, $to)
    {
        $from = $this->appRoot . $from;
        $to = $this->appRoot . $to;

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
        if (!file_exists($this->appRoot . $caminho))
            return false;

        unlink($this->appRoot . $caminho);
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

        if (is_array($base64)) {
            return $this->generateFileNameBase64Array($base64);
        }
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

    public function generateFileNameBase64Array(array $base64)
    {

        if(!isset($base64['type'])){
            return null ; 
        }
        $mimeParts = explode('/', $base64['type']);

        if (!isset($mimeParts[1])) {
            return null;
        }

        $extension = $mimeParts[1];

        return md5(uniqid(time()) . (new FuncoesLib())->pegaIpUsuario()) . "." . $extension;
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
        $caminhoImagem = $this->appRoot . $destino . $fileImage;
        $tcpdf = new TcpdfLib();

        $namePdf = $this->generateFileName();
        $caminhoPdf = $this->appRoot . $destino . $namePdf;
        $tcpdf->imageToPdf($caminhoImagem, $caminhoPdf);
        $this->removeFile($caminhoImagem);

        return $namePdf;
    }


    function convertImgToPdfBase64($caminhoImagem, $destino)
    {
        $tcpdf = new TcpdfLib();

        $namePdf = $this->generateFileName();
        $caminhoPdf = $this->appRoot . $destino . $namePdf;
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
    function uploadImage($file, $destinoFoto, $fotoAtual = "default.png", $imgDefault = "default.png", $widthImg = 1080)
    {
        preg_match("/\.(gif|bmp|png|jpg|jpeg|webp|avif)$/i", $file["name"], $ext);

        if (empty($ext[1]))
            return $imgDefault;

        $nome_imagem = md5(uniqid(time())) . "." . $ext[1];
        $toImage = $this->appRoot . $destinoFoto . $nome_imagem;

        $fromImage = $this->resizeCompressImage($file["tmp_name"], $ext[1], $widthImg) ?? $file["tmp_name"];
        move_uploaded_file($fromImage, $toImage);

        if ($fotoAtual != $imgDefault && !empty($fotoAtual))
            $this->removeFile($destinoFoto . $fotoAtual);

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
            $uploadPath = $this->appRoot . $toImage;

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
    public function resizeCompressImage($caminho_imagem, $extensao, $widthImg = null)
    {
        try {
            $ext = strtolower($extensao);

            if ($ext === 'jpeg' || $ext === 'jpg')
                $imagem = @imagecreatefromjpeg($caminho_imagem);
            elseif ($ext === 'png')
                $imagem = @imagecreatefrompng($caminho_imagem);
            elseif ($ext === 'gif')
                $imagem = @imagecreatefromgif($caminho_imagem);
            elseif ($ext === 'webp')
                $imagem = @imagecreatefromwebp($caminho_imagem);
            elseif ($ext === 'bmp')
                $imagem = @imagecreatefrombmp($caminho_imagem);
            elseif ($ext === 'avif' && function_exists('imagecreatefromavif'))
                $imagem = @imagecreatefromavif($caminho_imagem);
            else
                return $caminho_imagem;

            if (!$imagem)
                return $caminho_imagem;

            $angulo = 0;
            if (function_exists('exif_read_data')) {
                $exif = @exif_read_data($caminho_imagem);
                $orientacao = $exif['Orientation'] ?? ($exif['IFD0']['Orientation'] ?? 0);
                switch ($orientacao) {
                    case 8: $angulo = 90;  break;
                    case 3: $angulo = 180; break;
                    case 6: $angulo = -90; break;
                }
            }

            $largura = imagesx($imagem);
            $altura  = imagesy($imagem);

            if ($widthImg !== null) {
                $nova_largura = $widthImg;
                $nova_altura  = (int)(($altura * $widthImg) / $largura);
            } else {
                $nova_largura = $largura;
                $nova_altura  = $altura;
            }

            $nova_imagem = imagecreatetruecolor($nova_largura, $nova_altura);
            imagesavealpha($nova_imagem, true);
            imagefill($nova_imagem, 0, 0, imagecolorallocatealpha($nova_imagem, 0, 0, 0, 127));

            imagecopyresampled($nova_imagem, $imagem, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura, $altura);
            imagedestroy($imagem);

            if ($angulo !== 0) {
                $final = imagerotate($nova_imagem, $angulo, 0);
                imagedestroy($nova_imagem);
            } else {
                $final = $nova_imagem;
            }

            if (!$final)
                return $caminho_imagem;

            if ($ext === 'jpeg' || $ext === 'jpg')
                @imagejpeg($final, $caminho_imagem, 90);
            elseif ($ext === 'png')
                @imagepng($final, $caminho_imagem, 6);
            elseif ($ext === 'gif')
                @imagegif($final, $caminho_imagem);
            elseif ($ext === 'webp')
                @imagewebp($final, $caminho_imagem, 85);
            elseif ($ext === 'bmp')
                @imagebmp($final, $caminho_imagem);
            elseif ($ext === 'avif' && function_exists('imageavif'))
                @imageavif($final, $caminho_imagem);

            imagedestroy($final);

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
        if (!preg_match('/^image\/(pjpeg|jpeg|png|gif|bmp|jpg|webp|avif)$/', $file["type"]))
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
        $caminhoImagem = $this->appRoot . $to;

        if (!is_dir($caminhoImagem)) {
            mkdir($caminhoImagem, 0755, true);
        }

        $name = $this->generateFileNameBase64($string);

        $base64Image = $string;
        $imageData = explode(',', $base64Image)[1];
        $imageData = base64_decode($imageData);
        file_put_contents($caminhoImagem . $name, $imageData);

        return $name;
    }
}
