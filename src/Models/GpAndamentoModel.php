<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class GpAndamentoModel extends ModelAbstract
{
    Protected $CODANDAMENTO;
    Protected $CODFUNCIONARIO;
    Protected $TIPO;
    Protected $TITULO;
    Protected $DESCRICAO;
    Protected $RESPONSAVEL;
    Protected $NUMERO;
    Protected $DATAANDAMENTO;
    Protected $EXCLUIDO;
    
    public function getCODANDAMENTO()
    {
        return $this->CODANDAMENTO;
    }
    
    public function getCODFUNCIONARIO()
    {
        return $this->CODFUNCIONARIO;
    }
    
    public function getTIPO()
    {
        return $this->TIPO;
    }
    
    public function getTITULO()
    {
        return $this->TITULO;
    }
    
    public function getDESCRICAO()
    {
        return $this->DESCRICAO;
    }
    
    public function getRESPONSAVEL()
    {
        return $this->RESPONSAVEL;
    }
    
    public function getNUMERO()
    {
        return $this->NUMERO;
    }
    
    public function getDATAANDAMENTO()
    {
        return $this->DATAANDAMENTO;
    }
    
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }
    
    
    public function setCODANDAMENTO($CODANDAMENTO):self
    {
        $this->CODANDAMENTO = $CODANDAMENTO;
        return $this;
    }
    
    public function setCODFUNCIONARIO($CODFUNCIONARIO):self
    {
        $this->CODFUNCIONARIO = $CODFUNCIONARIO;
        return $this;
    }
    
    public function setTIPO($TIPO):self
    {
        $this->TIPO = $TIPO;
        return $this;
    }
    
    public function setTITULO($TITULO):self
    {
        $this->TITULO = $TITULO;
        return $this;
    }
    
    public function setDESCRICAO($DESCRICAO):self
    {
        $this->DESCRICAO = $DESCRICAO;
        return $this;
    }
    
    public function setRESPONSAVEL($RESPONSAVEL):self
    {
        $this->RESPONSAVEL = $RESPONSAVEL;
        return $this;
    }
    
    public function setNUMERO($NUMERO):self
    {
        $this->NUMERO = $NUMERO;
        return $this;
    }
    
    public function setDATAANDAMENTO($DATAANDAMENTO):self
    {
        $this->DATAANDAMENTO = $DATAANDAMENTO;
        return $this;
    }
    
    public function setEXCLUIDO($EXCLUIDO):self
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }
    
    
}