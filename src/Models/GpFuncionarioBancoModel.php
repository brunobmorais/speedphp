<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class GpFuncionarioBancoModel extends ModelAbstract
{
    Protected $CODFUNCIONARIO_BANCO;
    Protected $CODBANCO;
    Protected $CODFUNCIONARIO;
    Protected $TIPOCONTA;
    Protected $AGENCIA;
    Protected $CONTA;
    Protected $PIX_CHAVE;
    Protected $PIX_TIPO;
    Protected $SITUACAO;
    Protected $EXCLUIDO;
    
    public function getCODFUNCIONARIO_BANCO()
    {
        return $this->CODFUNCIONARIO_BANCO;
    }
    
    public function getCODBANCO()
    {
        return $this->CODBANCO;
    }
    
    public function getCODFUNCIONARIO()
    {
        return $this->CODFUNCIONARIO;
    }
    
    public function getTIPOCONTA()
    {
        return $this->TIPOCONTA;
    }
    
    public function getAGENCIA()
    {
        return $this->AGENCIA;
    }
    
    public function getCONTA()
    {
        return $this->CONTA;
    }
    
    public function getSITUACAO()
    {
        return $this->SITUACAO;
    }
    
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }
    
    
    public function setCODFUNCIONARIO_BANCO($CODFUNCIONARIO_BANCO):self
    {
        $this->CODFUNCIONARIO_BANCO = $CODFUNCIONARIO_BANCO;
        return $this;
    }
    
    public function setCODBANCO($CODBANCO):self
    {
        $this->CODBANCO = $CODBANCO;
        return $this;
    }
    
    public function setCODFUNCIONARIO($CODFUNCIONARIO):self
    {
        $this->CODFUNCIONARIO = $CODFUNCIONARIO;
        return $this;
    }
    
    public function setTIPOCONTA($TIPOCONTA):self
    {
        $this->TIPOCONTA = $TIPOCONTA;
        return $this;
    }
    
    public function setAGENCIA($AGENCIA):self
    {
        $this->AGENCIA = $AGENCIA;
        return $this;
    }
    
    public function setCONTA($CONTA):self
    {
        $this->CONTA = $CONTA;
        return $this;
    }
    
    public function setSITUACAO($SITUACAO):self
    {
        $this->SITUACAO = $SITUACAO;
        return $this;
    }
    
    public function setEXCLUIDO($EXCLUIDO):self
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPIXCHAVE()
    {
        return $this->PIX_CHAVE;
    }

    /**
     * @param mixed $PIX_CHAVE
     * @return GpFuncionarioBancoModel
     */
    public function setPIXCHAVE($PIX_CHAVE)
    {
        $this->PIX_CHAVE = $PIX_CHAVE;
        return $this;
    }


    
    
}