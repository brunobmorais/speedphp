<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class GpFuncionarioCargoModel extends ModelAbstract
{
    Protected $CODFUNCIONARIO_CARGO;
    Protected $CODPESSOA_CADASTRO;
    Protected $CODFUNCIONARIO;
    Protected $CODCARGO;
    Protected $SITUACAO;
    Protected $SALARIO;
    Protected $CARGAHORARIA;
    Protected $DATAADMISSAO;
    Protected $DATADEMISSAO;
    Protected $INSALUBRIDADE;
    Protected $ADICIONALNOTURNO;
    Protected $CATEGORIA_ESOCIAL;
    Protected $TIPOPREVIDENCIA;
    Protected $MATRICULA;
    Protected $PCD;
    Protected $EXCLUIDO;
    Protected $CRIADO_EM;
    Protected $ALTERADO_EM;
    
    public function getCODFUNCIONARIOCARGO()
    {
        return $this->CODFUNCIONARIO_CARGO;
    }
    
    public function getCODPESSOA_CADASTRO()
    {
        return $this->CODPESSOA_CADASTRO;
    }
    
    public function getCODFUNCIONARIO()
    {
        return $this->CODFUNCIONARIO;
    }
    
    public function getCODCARGO()
    {
        return $this->CODCARGO;
    }
    
    public function getSITUACAO()
    {
        return $this->SITUACAO;
    }
    
    public function getSALARIO()
    {
        return $this->SALARIO;
    }
    
    public function getCARGAHORARIA()
    {
        return $this->CARGAHORARIA;
    }
    
    public function getDATAADMISSAO()
    {
        return $this->DATAADMISSAO;
    }
    
    public function getDATADEMISSAO()
    {
        return $this->DATADEMISSAO;
    }
    
    public function getINSALUBRIDADE()
    {
        return $this->INSALUBRIDADE;
    }
    
    public function getADICIONALNOTURNO()
    {
        return $this->ADICIONALNOTURNO;
    }
    
    public function getCATEGORIAESOCIAL()
    {
        return $this->CATEGORIA_ESOCIAL;
    }
    
    public function getTIPOPREVIDENCIA()
    {
        return $this->TIPOPREVIDENCIA;
    }
    
    public function getMATRICULA()
    {
        return $this->MATRICULA;
    }
    
    public function getPCD()
    {
        return $this->PCD;
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
    
    
    public function setCODFUNCIONARIOCARGO($CODFUNCIONARIOCARGO):self
    {
        $this->CODFUNCIONARIO_CARGO = $CODFUNCIONARIOCARGO;
        return $this;
    }
    
    public function setCODPESSOA_CADASTRO($CODPESSOA_CADASTRO):self
    {
        $this->CODPESSOA_CADASTRO = $CODPESSOA_CADASTRO;
        return $this;
    }
    
    public function setCODFUNCIONARIO($CODFUNCIONARIO):self
    {
        $this->CODFUNCIONARIO = $CODFUNCIONARIO;
        return $this;
    }
    
    public function setCODCARGO($CODCARGO):self
    {
        $this->CODCARGO = $CODCARGO;
        return $this;
    }
    
    public function setSITUACAO($SITUACAO):self
    {
        $this->SITUACAO = $SITUACAO;
        return $this;
    }
    
    public function setSALARIO($SALARIO):self
    {
        $this->SALARIO = $SALARIO;
        return $this;
    }
    
    public function setCARGAHORARIA($CARGAHORARIA):self
    {
        $this->CARGAHORARIA = $CARGAHORARIA;
        return $this;
    }
    
    public function setDATAADMISSAO($DATAADMISSAO):self
    {
        $this->DATAADMISSAO = $DATAADMISSAO;
        return $this;
    }
    
    public function setDATADEMISSAO($DATADEMISSAO):self
    {
        $this->DATADEMISSAO = $DATADEMISSAO;
        return $this;
    }
    
    public function setINSALUBRIDADE($INSALUBRIDADE):self
    {
        $this->INSALUBRIDADE = $INSALUBRIDADE;
        return $this;
    }
    
    public function setADICIONALNOTURNO($ADICIONALNOTURNO):self
    {
        $this->ADICIONALNOTURNO = $ADICIONALNOTURNO;
        return $this;
    }
    
    public function setCATEGORIAESOCIAL($CATEGORIAESOCIAL):self
    {
        $this->CATEGORIA_ESOCIAL = $CATEGORIAESOCIAL;
        return $this;
    }
    
    public function setTIPOPREVIDENCIA($TIPOPREVIDENCIA):self
    {
        $this->TIPOPREVIDENCIA = $TIPOPREVIDENCIA;
        return $this;
    }
    
    public function setMATRICULA($MATRICULA):self
    {
        $this->MATRICULA = $MATRICULA;
        return $this;
    }
    
    public function setPCD($PCD):self
    {
        $this->PCD = $PCD;
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