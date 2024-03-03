-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Tempo de geração: 03/03/2024 às 01:40
-- Versão do servidor: 8.2.0
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Estrutura para tabela `MODULO`
--

CREATE TABLE `MODULO` (
  `CODMODULO` int NOT NULL,
  `TITULO` varchar(100) DEFAULT NULL,
  `DESCRICAO` varchar(200) DEFAULT NULL,
  `ICONE` varchar(100) DEFAULT NULL,
  `CONTROLLER` varchar(200) DEFAULT NULL,
  `ORDEM` int DEFAULT '1',
  `SITUACAO` int DEFAULT '1',
  `EXCLUIDO` int NOT NULL DEFAULT '0'
);

--
-- Despejando dados para a tabela `MODULO`
--

INSERT INTO `MODULO` (`CODMODULO`, `TITULO`, `DESCRICAO`, `ICONE`, `CONTROLLER`, `ORDEM`, `SITUACAO`, `EXCLUIDO`) VALUES
(1, 'Configurações', 'Cadastro de informações base do sistema', 'mdi-cog-outline', 'configuracoes', 1, 1, 0),
(2, 'Módulo 2', 'Teste', 'mdi-account-outline', 'tete', 1, 1, 0),
(3, 'Módulo 3', 'Teste', 'mdi-account-outline', 'teste', 1, 1, 0),
(4, 'Módulo 4', 'Teste', 'mdi-account-outline', 'tete', 1, 1, 0),
(5, 'Gestão de Pessoas', 'Cadastro de funcionários', 'mdi-account-group', 'gestaopessoas', 1, 1, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `PERFIL`
--

CREATE TABLE `PERFIL` (
  `CODPERFIL` int NOT NULL,
  `NOME` varchar(100) NOT NULL,
  `NIVEL` int NOT NULL DEFAULT '10',
  `EXCLUIDO` int NOT NULL DEFAULT '0'
) ;

--
-- Despejando dados para a tabela `PERFIL`
--

INSERT INTO `PERFIL` (`CODPERFIL`, `NOME`, `NIVEL`, `EXCLUIDO`) VALUES
(1, 'EXTERNO', 1, 0),
(2, 'TÉCNICO', 10, 0),
(3, 'COORDENADOR', 20, 0),
(4, 'GERENTE', 30, 0),
(5, 'DIRETOR', 40, 0),
(6, 'PRESIDENTE', 50, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `PERFIL_USUARIO`
--

CREATE TABLE `PERFIL_USUARIO` (
  `CODPERFIL_USUARIO` int NOT NULL,
  `CODUSUARIO` int NOT NULL,
  `CODPERFIL` int NOT NULL,
  `EXCLUIDO` int NOT NULL DEFAULT '0'
) ;

--
-- Despejando dados para a tabela `PERFIL_USUARIO`
--

INSERT INTO `PERFIL_USUARIO` (`CODPERFIL_USUARIO`, `CODUSUARIO`, `CODPERFIL`, `EXCLUIDO`) VALUES
(1, 1, 6, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `PESSOA`
--

CREATE TABLE `PESSOA` (
  `CODPESSOA` int NOT NULL,
  `NOME` varchar(200) DEFAULT NULL,
  `CPF` varchar(45) DEFAULT NULL,
  `EMAIL` varchar(200) DEFAULT NULL,
  `EXCLUIDO` int NOT NULL DEFAULT '0'
);

--
-- Despejando dados para a tabela `PESSOA`
--

INSERT INTO `PESSOA` (`CODPESSOA`, `NOME`, `CPF`, `EMAIL`, `EXCLUIDO`) VALUES
(1, 'Usuario Teste', '00000000000', 'brunomoraisti@gmail.com', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `PRIVILEGIO`
--

CREATE TABLE `PRIVILEGIO` (
  `CODPRIVILEGIO` int NOT NULL,
  `CODSERVICO` int NOT NULL,
  `CODPERFIL` int NOT NULL,
  `EXCLUIR` int NOT NULL DEFAULT '0',
  `LER` int NOT NULL DEFAULT '0',
  `SALVAR` int NOT NULL DEFAULT '0',
  `ALTERAR` int NOT NULL DEFAULT '0',
  `OUTROS` int NOT NULL DEFAULT '0',
  `EXCLUIDO` int NOT NULL DEFAULT '0'
);

--
-- Despejando dados para a tabela `PRIVILEGIO`
--

INSERT INTO `PRIVILEGIO` (`CODPRIVILEGIO`, `CODSERVICO`, `CODPERFIL`, `EXCLUIR`, `LER`, `SALVAR`, `ALTERAR`, `OUTROS`, `EXCLUIDO`) VALUES
(62, 1, 6, 1, 1, 1, 1, 1, 0),
(63, 2, 6, 1, 1, 1, 1, 1, 0),
(64, 3, 6, 1, 1, 1, 1, 1, 0),
(65, 4, 6, 1, 1, 1, 1, 1, 0),
(66, 5, 6, 1, 1, 1, 1, 1, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `SERVICO`
--

CREATE TABLE `SERVICO` (
  `CODSERVICO` int NOT NULL,
  `CODMODULO` int DEFAULT NULL,
  `TITULO` varchar(100) DEFAULT NULL,
  `DESCRICAO` varchar(200) DEFAULT NULL,
  `ICONE` varchar(100) DEFAULT NULL,
  `CONTROLLER` varchar(100) DEFAULT NULL,
  `ORDEM` int NOT NULL DEFAULT '1',
  `SITUACAO` int NOT NULL DEFAULT '1',
  `EXCLUIDO` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `SERVICO`
--

INSERT INTO `SERVICO` (`CODSERVICO`, `CODMODULO`, `TITULO`, `DESCRICAO`, `ICONE`, `CONTROLLER`, `ORDEM`, `SITUACAO`, `EXCLUIDO`) VALUES
(1, 1, 'Módulos', 'modulos', 'mdi mdi-archive-plus-outline', 'modulos', 1, 1, 0),
(2, 1, 'Serviços', 'Serviços', 'mdi mdi-archive-plus-outline', 'servicos', 1, 1, 0),
(3, 1, 'Usuários', 'Usuários do Sistema', 'mdi mdi-archive-plus-outline', 'usuarios', 1, 1, 0),
(4, 5, 'Funcionários', 'Cadastro de funcionários', 'mdi-account', 'funcionarios', 1, 1, 0),
(5, 1, 'Perfils', 'Pefil de usuários', 'mdi mdi-archive-plus-outline', 'perfil', 1, 1, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `USUARIO`
--

CREATE TABLE `USUARIO` (
  `CODUSUARIO` int NOT NULL,
  `CODPESSOA` int NOT NULL,
  `SENHA` varchar(200) DEFAULT NULL,
  `SITUACAO` int NOT NULL DEFAULT '0',
  `DATACADASTRO` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `EXCLUIDO` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Despejando dados para a tabela `USUARIO`
--

INSERT INTO `USUARIO` (`CODUSUARIO`, `CODPESSOA`, `SENHA`, `SITUACAO`, `DATACADASTRO`, `EXCLUIDO`) VALUES
(1, 1, '$2y$10$H92r2Spv1CAI4Gqu.d0xRO9eJsxbeskj8QyuRCHypq9aRtjCWn8M.', 1, '2023-01-01 00:00:00', 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `MODULO`
--
ALTER TABLE `MODULO`
  ADD PRIMARY KEY (`CODMODULO`);

--
-- Índices de tabela `PERFIL`
--
ALTER TABLE `PERFIL`
  ADD PRIMARY KEY (`CODPERFIL`);

--
-- Índices de tabela `PERFIL_USUARIO`
--
ALTER TABLE `PERFIL_USUARIO`
  ADD PRIMARY KEY (`CODPERFIL_USUARIO`),
  ADD KEY `fk_PERFIL_USUARIO_USUARIO1_idx` (`CODUSUARIO`),
  ADD KEY `fk_PERFIL_USUARIO_PERFIL1_idx` (`CODPERFIL`);

--
-- Índices de tabela `PESSOA`
--
ALTER TABLE `PESSOA`
  ADD PRIMARY KEY (`CODPESSOA`);

--
-- Índices de tabela `PRIVILEGIO`
--
ALTER TABLE `PRIVILEGIO`
  ADD PRIMARY KEY (`CODPRIVILEGIO`),
  ADD KEY `FK_PRIVILEGIO_SERVICO_idx` (`CODSERVICO`),
  ADD KEY `fk_PRIVILEGIO_PERFIL1_idx` (`CODPERFIL`);

--
-- Índices de tabela `SERVICO`
--
ALTER TABLE `SERVICO`
  ADD PRIMARY KEY (`CODSERVICO`),
  ADD KEY `FK_SERVICO_MODULO_idx` (`CODMODULO`);

--
-- Índices de tabela `USUARIO`
--
ALTER TABLE `USUARIO`
  ADD PRIMARY KEY (`CODUSUARIO`),
  ADD KEY `FK_USUARIO_PESSOA_idx` (`CODPESSOA`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `MODULO`
--
ALTER TABLE `MODULO`
  MODIFY `CODMODULO` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `PERFIL`
--
ALTER TABLE `PERFIL`
  MODIFY `CODPERFIL` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `PERFIL_USUARIO`
--
ALTER TABLE `PERFIL_USUARIO`
  MODIFY `CODPERFIL_USUARIO` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `PESSOA`
--
ALTER TABLE `PESSOA`
  MODIFY `CODPESSOA` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `PRIVILEGIO`
--
ALTER TABLE `PRIVILEGIO`
  MODIFY `CODPRIVILEGIO` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT de tabela `SERVICO`
--
ALTER TABLE `SERVICO`
  MODIFY `CODSERVICO` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `USUARIO`
--
ALTER TABLE `USUARIO`
  MODIFY `CODUSUARIO` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `PERFIL_USUARIO`
--
ALTER TABLE `PERFIL_USUARIO`
  ADD CONSTRAINT `fk_PERFIL_USUARIO_PERFIL` FOREIGN KEY (`CODPERFIL`) REFERENCES `PERFIL` (`CODPERFIL`),
  ADD CONSTRAINT `fk_PERFIL_USUARIO_USUARIO1` FOREIGN KEY (`CODUSUARIO`) REFERENCES `USUARIO` (`CODUSUARIO`);

--
-- Restrições para tabelas `PRIVILEGIO`
--
ALTER TABLE `PRIVILEGIO`
  ADD CONSTRAINT `FK_PRIVILEGIO_PERFIL` FOREIGN KEY (`CODPERFIL`) REFERENCES `PERFIL` (`CODPERFIL`),
  ADD CONSTRAINT `FK_PRIVILEGIO_SERVICO` FOREIGN KEY (`CODSERVICO`) REFERENCES `SERVICO` (`CODSERVICO`);

--
-- Restrições para tabelas `SERVICO`
--
ALTER TABLE `SERVICO`
  ADD CONSTRAINT `FK_SERVICO_MODULO` FOREIGN KEY (`CODMODULO`) REFERENCES `MODULO` (`CODMODULO`);

--
-- Restrições para tabelas `USUARIO`
--
ALTER TABLE `USUARIO`
  ADD CONSTRAINT `FK_USUARIO_PESSOA` FOREIGN KEY (`CODPESSOA`) REFERENCES `PESSOA` (`CODPESSOA`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
