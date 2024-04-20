<?php
namespace App\Models;

use BMorais\Database\ModelAbstract;

class UsuarioModel extends ModelAbstract {


    protected $CODUSUARIO;
    protected $CODPESSOA;
    protected $SENHA;
    protected $ULTIMOACESSO;
    protected $REDEFINIRSENHA;
    protected $SITUACAO;
    protected $EXCLUIDO;

    protected $CPF;
    protected $NOME;
    protected $DATANASCIMENTO;
    protected $SEXO;
    protected $TELEFONE;
    protected $EMAIL;

    /**
     * @return mixed
     */
    public function getCODUSUARIO()
    {
        return $this->CODUSUARIO;
    }

    /**
     * @param mixed $CODUSUARIO
     * @return UsuarioModel
     */
    public function setCODUSUARIO($CODUSUARIO)
    {
        $this->CODUSUARIO = $CODUSUARIO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCODPESSOA()
    {
        return $this->CODPESSOA;
    }

    /**
     * @param mixed $CODPESSOA
     * @return UsuarioModel
     */
    public function setCODPESSOA($CODPESSOA)
    {
        $this->CODPESSOA = $CODPESSOA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSENHA()
    {
        return $this->SENHA;
    }

    /**
     * @param mixed $SENHA
     * @return UsuarioModel
     */
    public function setSENHA($SENHA)
    {
        $this->SENHA = $SENHA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getULTIMOACESSO()
    {
        return $this->ULTIMOACESSO;
    }

    /**
     * @param mixed $ULTIMOACESSO
     * @return UsuarioModel
     */
    public function setULTIMOACESSO($ULTIMOACESSO)
    {
        $this->ULTIMOACESSO = $ULTIMOACESSO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getREDEFINIRSENHA()
    {
        return $this->REDEFINIRSENHA;
    }

    /**
     * @param mixed $REDEFINIRSENHA
     * @return UsuarioModel
     */
    public function setREDEFINIRSENHA($REDEFINIRSENHA)
    {
        $this->REDEFINIRSENHA = $REDEFINIRSENHA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSITUACAO()
    {
        return $this->SITUACAO;
    }

    /**
     * @param mixed $SITUACAO
     * @return UsuarioModel
     */
    public function setSITUACAO($SITUACAO)
    {
        $this->SITUACAO = $SITUACAO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }

    /**
     * @param mixed $EXCLUIDO
     * @return UsuarioModel
     */
    public function setEXCLUIDO($EXCLUIDO)
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCPF()
    {
        return $this->CPF;
    }

    /**
     * @param mixed $CPF
     * @return UsuarioModel
     */
    public function setCPF($CPF)
    {
        $this->CPF = $CPF;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNOME()
    {
        return $this->NOME;
    }

    /**
     * @param mixed $NOME
     * @return UsuarioModel
     */
    public function setNOME($NOME)
    {
        $this->NOME = $NOME;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDATANASCIMENTO()
    {
        return $this->DATANASCIMENTO;
    }

    /**
     * @param mixed $DATANASCIMENTO
     * @return UsuarioModel
     */
    public function setDATANASCIMENTO($DATANASCIMENTO)
    {
        $this->DATANASCIMENTO = $DATANASCIMENTO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSEXO()
    {
        return $this->SEXO;
    }

    /**
     * @param mixed $SEXO
     * @return UsuarioModel
     */
    public function setSEXO($SEXO)
    {
        $this->SEXO = $SEXO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTELEFONE()
    {
        return $this->TELEFONE;
    }

    /**
     * @param mixed $TELEFONE
     * @return UsuarioModel
     */
    public function setTELEFONE($TELEFONE)
    {
        $this->TELEFONE = $TELEFONE;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEMAIL()
    {
        return $this->EMAIL;
    }

    /**
     * @param mixed $EMAIL
     * @return UsuarioModel
     */
    public function setEMAIL($EMAIL)
    {
        $this->EMAIL = $EMAIL;
        return $this;
    }


}
