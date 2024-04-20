<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class GpBancoModel extends ModelAbstract
{
    Protected $CODBANCO;
    Protected $CODIGO;
    Protected $NOMELONGO;
    Protected $NOMECURTO;
    Protected $EXCLUIDO;
    
    public function getCODBANCO()
    {
        return $this->CODBANCO;
    }
    
    public function getCODIGO()
    {
        return $this->CODIGO;
    }
    
    public function getNOMELONGO()
    {
        return $this->NOMELONGO;
    }
    
    public function getNOMECURTO()
    {
        return $this->NOMECURTO;
    }
    
    public function getEXCLUIDO()
    {
        return $this->EXCLUIDO;
    }
    
    
    public function setCODBANCO($CODBANCO):self
    {
        $this->CODBANCO = $CODBANCO;
        return $this;
    }
    
    public function setCODIGO($CODIGO):self
    {
        $this->CODIGO = $CODIGO;
        return $this;
    }
    
    public function setNOMELONGO($NOMELONGO):self
    {
        $this->NOMELONGO = $NOMELONGO;
        return $this;
    }
    
    public function setNOMECURTO($NOMECURTO):self
    {
        $this->NOMECURTO = $NOMECURTO;
        return $this;
    }
    
    public function setEXCLUIDO($EXCLUIDO):self
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }
    
    
}