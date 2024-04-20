<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class SiPerfilUsuarioModel extends ModelAbstract
{
    Protected $CODPERFIL_USUARIO;
    Protected $CODUSUARIO;
    Protected $CODPERFIL;
    Protected $EXCLUIDO;
    Protected $CRIADO_EM;
    Protected $ALTERADO_EM;
    
    public function getCODPERFILUSUARIO()
    {
        return $this->CODPERFIL_USUARIO;
    }
    
    public function getCODUSUARIO()
    {
        return $this->CODUSUARIO;
    }
    
    public function getCODPERFIL()
    {
        return $this->CODPERFIL;
    }
    
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }
    
    public function getCRIADOEM()
    {
        return $this->CRIADO_EM;
    }
    
    public function getALTERADOEM()
    {
        return $this->ALTERADO_EM;
    }
    
    
    public function setCODPERFILUSUARIO($CODPERFILUSUARIO):self
    {
        $this->CODPERFIL_USUARIO = $CODPERFILUSUARIO;
        return $this;
    }
    
    public function setCODUSUARIO($CODUSUARIO):self
    {
        $this->CODUSUARIO = $CODUSUARIO;
        return $this;
    }
    
    public function setCODPERFIL($CODPERFIL):self
    {
        $this->CODPERFIL = $CODPERFIL;
        return $this;
    }
    
    public function setEXCLUIDO($EXCLUIDO):self
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }
    
    public function setCRIADOEM($CRIADOEM):self
    {
        $this->CRIADO_EM = $CRIADOEM;
        return $this;
    }
    
    public function setALTERADOEM($ALTERADOEM):self
    {
        $this->ALTERADO_EM = $ALTERADOEM;
        return $this;
    }
    
    
}