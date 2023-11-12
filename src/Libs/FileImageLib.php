<?php

namespace App\Libs;

use App\Libs\Tcpdf\TcpdfLib;

class FileImageLib extends FileLib
{
    /**
     * FUNÇÃO PARA CONVERTER UMA IMAGEM EM PDF
     *
     * @param $arquivo
     * @param $destino
     * @param $tcpdf
     * @return string
     */
    function convertImgToPdf($arquivo, $destino)
    {
        $fileImage = $this->uploadImage($arquivo, $destino, "padrao.png");
        $caminhoImagem = $_SERVER['DOCUMENT_ROOT'] . $destino . $fileImage;
        $tcpdf = new TcpdfLib();

        $imgtopdf = $this->generateFileName();
        $tcpdf->imageToPdf($caminhoImagem, $destinoPdf);
        $this->removeFile($caminhoImagem);

        return $imgtopdf;
    }

    /**
     * FUNÇÃO ENVIAR IMAGEM
     *
     * @param $destinoFoto
     * @param $foto
     * @param $fotoAtual
     *
     * @return string
     */
    function uploadImage($foto, $destinoFoto, $fotoAtual)
    {


        // Se não houver nenhum erro

        // Pega extensão da imagem
        preg_match("/\.(gif|bmp|png|jpg|jpeg){1}$/i", $foto["name"], $ext);

        // Gera um nome único para a imagem
        $nome_imagem = md5(uniqid(time())) . "." . $ext[1];

        // Caminho de onde ficará a imagem
        $caminho_imagem = $destinoFoto . "" . $nome_imagem;

        $caminho_imagem = $_SERVER['DOCUMENT_ROOT'] . $caminho_imagem;
        //echo $caminho_imagem; exit;

        // Faz o upload da imagem para seu respectivo caminho
        move_uploaded_file($this->resizeImage($foto["tmp_name"], $ext[1]), $caminho_imagem);
        //move_uploaded_file($foto["tmp_name"], $caminho_imagem);


        //REMOVE IMAGEM ANTIGA
        if ($fotoAtual <> "padrao.png") {
            $this->removeFile($destinoFoto . $fotoAtual);
        }

        return $nome_imagem;
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

        // Retorna o identificador da imagem
        if ($extensao == 'jpeg' || $extensao == 'jpg' || $extensao == 'JPG' || $extensao == 'JPEG')
            $imagem = @imagecreatefromjpeg($caminho_imagem);
        else if ($extensao == 'png' || $extensao == 'PNG')
            $imagem = @imagecreatefrompng($caminho_imagem);
        else if ($extensao == 'gif' || $extensao == 'GIF')
            $imagem = @imagecreatefromgif($caminho_imagem);

        // PEGA VALORES PARA ROTAÇÃO
        $exif = exif_read_data($caminho_imagem);
        if(!empty($exif['Orientation'])) {
            switch($exif['Orientation']) {
                case 8:
                    $angulo = 90;
                    break;
                case 3:
                    $angulo = 180;
                    break;
                case 6:
                    $angulo = -90;
                    break;
            }
        }

        // Cria duas variáveis com a largura e altura da imagem
        list($largura, $altura) = @getimagesize($caminho_imagem);

        // Nova largura e altura
        $proporcao = 600;
        $nova_largura = $proporcao;
        $nova_altura = ($altura * $proporcao) / $largura;

        // Cria uma nova imagem em branco
        $nova_imagem = @imagecreatetruecolor($nova_largura, $nova_altura);
        imagesavealpha($nova_imagem, true);
        $cor_fundo = imagecolorallocatealpha($nova_imagem, 0, 0, 0, 127);
        imagefill($nova_imagem, 0, 0, $cor_fundo);


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

        $imgRotation = imagerotate($nova_imagem,$angulo,0);

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
    }
}