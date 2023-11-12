<?php

namespace App\Libs;

class FileLib{

    /**
     * FUNÇÃO ENVIAR ARQUIVOS DIVERSOS
     *
     * @param $arquivo
     * @param $destino
     * @return string
     */
    public function uploadFile($arquivo, $destino)
    {

        // Pega extensão da imagem
        $nomeArquivo = $this->generateFileName($arquivo['name']);

        // Caminho de onde ficará a imagem
        $caminhoArquivo = $destino . "" . $nomeArquivo;
        $caminhoArquivo = $_SERVER['DOCUMENT_ROOT'] . $caminhoArquivo;


        // Faz o upload da imagem para seu respectivo caminho
        move_uploaded_file($arquivo["tmp_name"], $caminhoArquivo);

        return $nomeArquivo;
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
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $caminho))
            unlink($_SERVER['DOCUMENT_ROOT'] . $caminho);
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
}