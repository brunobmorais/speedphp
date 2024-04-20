<?php
namespace App\Models;
use BMorais\Database\ModelAbstract;

class GpDocumentoModel extends ModelAbstract
{
    Protected $CODDOCUMENTO;
    Protected $CODPESSOA_CADASTRO;
    Protected $CODFUNCIONARIO;
    Protected $ESCOLARIDADE;
    Protected $NATURALIDADE;
    Protected $ESTADOCIVIL;
    Protected $NOMEPAI;
    Protected $NOMEMAE;
    Protected $PISPASEP;
    Protected $NUMERO_CTB;
    Protected $SERIE_CTB;
    Protected $ESTADO_CTB;
    Protected $NUMERO_RG;
    Protected $ORGAO_RG;
    Protected $DATAEXPEDICAO_RG;
    Protected $NUMERO_CNH;
    Protected $VALIDADE_CNH;
    Protected $CATEGORIA_CNH;
    Protected $TE_NUMERO;
    Protected $TE_ZONA;
    Protected $TE_SESSAO;
    Protected $TE_DATAEMISSAO;
    Protected $TE_CODCIDADE;
    Protected $EXCLUIDO;
    Protected $CRIADO_EM;
    Protected $ALTERADO_EM;

    /**
     * @return mixed
     */
    public function getCODDOCUMENTO()
    {
        return $this->CODDOCUMENTO;
    }

    /**
     * @param mixed $CODDOCUMENTO
     * @return GpDocumentoModel
     */
    public function setCODDOCUMENTO($CODDOCUMENTO)
    {
        $this->CODDOCUMENTO = $CODDOCUMENTO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCODPESSOA_CADASTRO()
    {
        return $this->CODPESSOA_CADASTRO;
    }

    /**
     * @param mixed $CODPESSOA_CADASTRO
     * @return GpDocumentoModel
     */
    public function setCODPESSOACADASTRO($CODPESSOA_CADASTRO)
    {
        $this->CODPESSOA_CADASTRO = $CODPESSOA_CADASTRO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCODFUNCIONARIO()
    {
        return $this->CODFUNCIONARIO;
    }

    /**
     * @param mixed $CODFUNCIONARIO
     * @return GpDocumentoModel
     */
    public function setCODFUNCIONARIO($CODFUNCIONARIO)
    {
        $this->CODFUNCIONARIO = $CODFUNCIONARIO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getESCOLARIDADE()
    {
        return $this->ESCOLARIDADE;
    }

    /**
     * @param mixed $ESCOLARIDADE
     * @return GpDocumentoModel
     */
    public function setESCOLARIDADE($ESCOLARIDADE)
    {
        $this->ESCOLARIDADE = $ESCOLARIDADE;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNATURALIDADE()
    {
        return $this->NATURALIDADE;
    }

    /**
     * @param mixed $NATURALIDADE
     * @return GpDocumentoModel
     */
    public function setNATURALIDADE($NATURALIDADE)
    {
        $this->NATURALIDADE = $NATURALIDADE;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getESTADOCIVIL()
    {
        return $this->ESTADOCIVIL;
    }

    /**
     * @param mixed $ESTADOCIVIL
     * @return GpDocumentoModel
     */
    public function setESTADOCIVIL($ESTADOCIVIL)
    {
        $this->ESTADOCIVIL = $ESTADOCIVIL;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNOMEPAI()
    {
        return $this->NOMEPAI;
    }

    /**
     * @param mixed $NOMEPAI
     * @return GpDocumentoModel
     */
    public function setNOMEPAI($NOMEPAI)
    {
        $this->NOMEPAI = $NOMEPAI;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNOMEMAE()
    {
        return $this->NOMEMAE;
    }

    /**
     * @param mixed $NOMEMAE
     * @return GpDocumentoModel
     */
    public function setNOMEMAE($NOMEMAE)
    {
        $this->NOMEMAE = $NOMEMAE;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPISPASEP()
    {
        return $this->PISPASEP;
    }

    /**
     * @param mixed $PISPASEP
     * @return GpDocumentoModel
     */
    public function setPISPASEP($PISPASEP)
    {
        $this->PISPASEP = $PISPASEP;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNUMEROCTB()
    {
        return $this->NUMERO_CTB;
    }

    /**
     * @param mixed $NUMERO_CTB
     * @return GpDocumentoModel
     */
    public function setNUMEROCTB($NUMERO_CTB)
    {
        $this->NUMERO_CTB = $NUMERO_CTB;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSERIECTB()
    {
        return $this->SERIE_CTB;
    }

    /**
     * @param mixed $SERIE_CTB
     * @return GpDocumentoModel
     */
    public function setSERIECTB($SERIE_CTB)
    {
        $this->SERIE_CTB = $SERIE_CTB;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getESTADOCTB()
    {
        return $this->ESTADO_CTB;
    }

    /**
     * @param mixed $ESTADO_CTB
     * @return GpDocumentoModel
     */
    public function setESTADOCTB($ESTADO_CTB)
    {
        $this->ESTADO_CTB = $ESTADO_CTB;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNUMERORG()
    {
        return $this->NUMERO_RG;
    }

    /**
     * @param mixed $NUMERO_RG
     * @return GpDocumentoModel
     */
    public function setNUMERORG($NUMERO_RG)
    {
        $this->NUMERO_RG = $NUMERO_RG;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getORGAORG()
    {
        return $this->ORGAO_RG;
    }

    /**
     * @param mixed $ORGAO_RG
     * @return GpDocumentoModel
     */
    public function setORGAORG($ORGAO_RG)
    {
        $this->ORGAO_RG = $ORGAO_RG;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDATAEXPEDICAORG()
    {
        return $this->DATAEXPEDICAO_RG;
    }

    /**
     * @param mixed $DATAEXPEDICAO_RG
     * @return GpDocumentoModel
     */
    public function setDATAEXPEDICAORG($DATAEXPEDICAO_RG)
    {
        $this->DATAEXPEDICAO_RG = $DATAEXPEDICAO_RG;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNUMEROCNH()
    {
        return $this->NUMERO_CNH;
    }

    /**
     * @param mixed $NUMERO_CNH
     * @return GpDocumentoModel
     */
    public function setNUMEROCNH($NUMERO_CNH)
    {
        $this->NUMERO_CNH = $NUMERO_CNH;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVALIDADECNH()
    {
        return $this->VALIDADE_CNH;
    }

    /**
     * @param mixed $VALIDADE_CNH
     * @return GpDocumentoModel
     */
    public function setVALIDADECNH($VALIDADE_CNH)
    {
        $this->VALIDADE_CNH = $VALIDADE_CNH;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCATEGORIACNH()
    {
        return $this->CATEGORIA_CNH;
    }

    /**
     * @param mixed $CATEGORIA_CNH
     * @return GpDocumentoModel
     */
    public function setCATEGORIACNH($CATEGORIA_CNH)
    {
        $this->CATEGORIA_CNH = $CATEGORIA_CNH;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTENUMERO()
    {
        return $this->TE_NUMERO;
    }

    /**
     * @param mixed $TE_NUMERO
     * @return GpDocumentoModel
     */
    public function setTENUMERO($TE_NUMERO)
    {
        $this->TE_NUMERO = $TE_NUMERO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTEZONA()
    {
        return $this->TE_ZONA;
    }

    /**
     * @param mixed $TE_ZONA
     * @return GpDocumentoModel
     */
    public function setTEZONA($TE_ZONA)
    {
        $this->TE_ZONA = $TE_ZONA;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTESESSAO()
    {
        return $this->TE_SESSAO;
    }

    /**
     * @param mixed $TE_SESSAO
     * @return GpDocumentoModel
     */
    public function setTESESSAO($TE_SESSAO)
    {
        $this->TE_SESSAO = $TE_SESSAO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTEDATAEMISSAO()
    {
        return $this->TE_DATAEMISSAO;
    }

    /**
     * @param mixed $TE_DATAEMISSAO
     * @return GpDocumentoModel
     */
    public function setTEDATAEMISSAO($TE_DATAEMISSAO)
    {
        $this->TE_DATAEMISSAO = $TE_DATAEMISSAO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTECODCIDADE()
    {
        return $this->TE_CODCIDADE;
    }

    /**
     * @param mixed $TE_CODCIDADE
     * @return GpDocumentoModel
     */
    public function setTECODCIDADE($TE_CODCIDADE)
    {
        $this->TE_CODCIDADE = $TE_CODCIDADE;
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
     * @return GpDocumentoModel
     */
    public function setEXCLUIDO($EXCLUIDO)
    {
        $this->EXCLUIDO = $EXCLUIDO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCRIADOEM()
    {
        return $this->CRIADO_EM;
    }

    /**
     * @param mixed $CRIADO_EM
     * @return GpDocumentoModel
     */
    public function setCRIADOEM($CRIADO_EM)
    {
        $this->CRIADO_EM = $CRIADO_EM;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getALTERADOEM()
    {
        return $this->ALTERADO_EM;
    }

    /**
     * @param mixed $ALTERADO_EM
     * @return GpDocumentoModel
     */
    public function setALTERADOEM($ALTERADO_EM)
    {
        $this->ALTERADO_EM = $ALTERADO_EM;
        return $this;
    }
    
    
}