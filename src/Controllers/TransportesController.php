<?php

require_once(__DIR__ . "/../repositories/TransportesRepository.php");

class TransportesController
{
    private const TIPOS = [
        "carro" => ["nome" => "Carro (média de referência)", "fator" => 0.0982, "unidade_referencia" => "kg CO2e/passageiro-km", "atividade" => "passageiro-km", "fonte" => "BNDES, Descarbonização dos transportes, Tabela 3", "url_fonte" => "https://web.bndes.gov.br/bib/jspui/bitstream/1408/27347/3/PRLiv%20216297_Descarboniza%C3%A7%C3%A3o%20dos%20transportes.pdf", "ano" => 2024, "escopo" => "Emissão operacional média; ocupação média reportada de 1,2 passageiros."],
        "moto" => ["nome" => "Motocicleta (média de referência)", "fator" => 0.0711, "unidade_referencia" => "kg CO2e/passageiro-km", "atividade" => "passageiro-km", "fonte" => "IPEA, Comunicados do Ipea n.º 113, Tabela 2", "url_fonte" => "https://repositorio.ipea.gov.br/bitstream/11058/5281/1/Comunicados_n113_Polui%C3%A7%C3%A3o.pdf", "ano" => 2011, "escopo" => "Média de referência brasileira publicada em 2011; frota e uso podem variar."],
        "onibus_diesel" => ["nome" => "Ônibus urbano a diesel (média de referência)", "fator" => 0.0195, "unidade_referencia" => "kg CO2e/passageiro-km", "atividade" => "passageiro-km", "fonte" => "BNDES, Descarbonização dos transportes, Tabela 3", "url_fonte" => "https://web.bndes.gov.br/bib/jspui/bitstream/1408/27347/3/PRLiv%20216297_Descarboniza%C3%A7%C3%A3o%20dos%20transportes.pdf", "ano" => 2024, "escopo" => "Emissão operacional média; ocupação média reportada de 60 passageiros."],
        "onibus_eletrico" => ["nome" => "Ônibus urbano elétrico (média de referência)", "fator" => 0.0026, "unidade_referencia" => "kg CO2e/passageiro-km", "atividade" => "passageiro-km", "fonte" => "BNDES, Descarbonização dos transportes, Tabela 3", "url_fonte" => "https://web.bndes.gov.br/bib/jspui/bitstream/1408/27347/3/PRLiv%20216297_Descarboniza%C3%A7%C3%A3o%20dos%20transportes.pdf", "ano" => 2024, "escopo" => "Emissão média reportada; ocupação de 60 passageiros e eletricidade considerada pela fonte."],
        "metro" => ["nome" => "Metrô (média de referência)", "fator" => 0.0014, "unidade_referencia" => "kg CO2e/passageiro-km", "atividade" => "passageiro-km", "fonte" => "BNDES, Descarbonização dos transportes, Tabela 3", "url_fonte" => "https://web.bndes.gov.br/bib/jspui/bitstream/1408/27347/3/PRLiv%20216297_Descarboniza%C3%A7%C3%A3o%20dos%20transportes.pdf", "ano" => 2024, "escopo" => "Emissão operacional média; ocupação média reportada de 1.500 passageiros."],
        "caminhada" => ["nome" => "Caminhada", "fator" => 0.0, "unidade_referencia" => "kg CO2e/passageiro-km", "atividade" => "passageiro-km", "fonte" => "Hipótese operacional simplificada do projeto", "ano" => 2026, "escopo" => "Emissões diretas de operação; alimentação, fabricação e infraestrutura excluídas."],
        "bicicleta" => ["nome" => "Bicicleta", "fator" => 0.0, "unidade_referencia" => "kg CO2e/passageiro-km", "atividade" => "passageiro-km", "fonte" => "Hipótese operacional simplificada do projeto", "ano" => 2026, "escopo" => "Emissões diretas de operação; fabricação, manutenção e infraestrutura excluídas."],
        "caminhao" => ["nome" => "Caminhão — carga geral rodoviária", "fator" => 0.0544, "unidade_referencia" => "kg CO2/tonelada-km", "atividade" => "tonelada-km", "fonte" => "BNDES, Descarbonização dos transportes, Tabela 1 (base EPL/Iema)", "url_fonte" => "https://web.bndes.gov.br/bib/jspui/bitstream/1408/27347/3/PRLiv%20216297_Descarboniza%C3%A7%C3%A3o%20dos%20transportes.pdf", "ano" => 2021, "escopo" => "Média de carga geral; CO2 de operação, diesel B10."],
        "ferrovia_carga" => ["nome" => "Ferrovia — carga geral", "fator" => 0.0238, "unidade_referencia" => "kg CO2/tonelada-km", "atividade" => "tonelada-km", "fonte" => "BNDES, Descarbonização dos transportes, Tabela 1 (base EPL/Iema)", "url_fonte" => "https://web.bndes.gov.br/bib/jspui/bitstream/1408/27347/3/PRLiv%20216297_Descarboniza%C3%A7%C3%A3o%20dos%20transportes.pdf", "ano" => 2021, "escopo" => "Média de carga geral; CO2 de operação, diesel B10."],
        "cabotagem_carga" => ["nome" => "Navio — cabotagem de carga geral", "fator" => 0.0091, "unidade_referencia" => "kg CO2/tonelada-km", "atividade" => "tonelada-km", "fonte" => "BNDES, Descarbonização dos transportes, Tabela 1 (base EPL/Iema)", "url_fonte" => "https://web.bndes.gov.br/bib/jspui/bitstream/1408/27347/3/PRLiv%20216297_Descarboniza%C3%A7%C3%A3o%20dos%20transportes.pdf", "ano" => 2021, "escopo" => "Média de carga geral e mistura de combustível indicada pela fonte."],
        "hidrovia_carga" => ["nome" => "Hidrovia — carga geral", "fator" => 0.0074, "unidade_referencia" => "kg CO2/tonelada-km", "atividade" => "tonelada-km", "fonte" => "BNDES, Descarbonização dos transportes, Tabela 1 (base EPL/Iema)", "url_fonte" => "https://web.bndes.gov.br/bib/jspui/bitstream/1408/27347/3/PRLiv%20216297_Descarboniza%C3%A7%C3%A3o%20dos%20transportes.pdf", "ano" => 2021, "escopo" => "Média de carga geral e mistura de combustível indicada pela fonte."],
    ];

    private $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /* Multiplica atividade em passageiro-km ou tonelada-km pelo fator correspondente. */
    public function calcularEmissao($atividade, $fator_emissao)
    {
        return $atividade * $fator_emissao;
    }

    /* Valida os dados, calcula com o fator versionado em PHP e registra a atividade. */
    public function adicionarTransporte($id_simulacao, $tipo, $distancia_km, $carga_toneladas = null)
    {
        if (
            filter_var($id_simulacao, FILTER_VALIDATE_INT) === false ||
            (int) $id_simulacao <= 0 ||
            !is_string($tipo) ||
            !isset(self::TIPOS[$tipo]) ||
            !is_numeric($distancia_km) ||
            !is_finite((float) $distancia_km) ||
            (float) $distancia_km <= 0 ||
            (float) $distancia_km > 99999999.9999
        ) {
            return false;
        }

        $tipoInfo = self::TIPOS[$tipo];
        $distancia_km = (float) $distancia_km;
        $fator = (float) $tipoInfo["fator"];
        $atividade = $distancia_km;

        if ($tipoInfo["atividade"] === "tonelada-km") {
            if (!is_numeric($carga_toneladas) || !is_finite((float) $carga_toneladas) || (float) $carga_toneladas <= 0 || (float) $carga_toneladas > 1000000) return false;
            $atividade *= (float) $carga_toneladas;
        }
        if (!is_finite($atividade) || $atividade > 99999999.99) return false;

        if (!is_finite($fator) || $fator < 0) {
            return false;
        }

        $emissao = $this->calcularEmissao($atividade, $fator);

        /* Limite compatível com a coluna atual, que guarda centi-kg nesta trilha. */
        if (
            !is_finite($emissao) ||
            $emissao < 0 ||
            $emissao > 999999.99
        ) {
            return false;
        }

        return $this->repository->adicionarEstimativaTransporte((int) $id_simulacao, $tipo, $atividade, $fator, $emissao);
    }

    public function listarTiposTransportes()
    {
        $tipos = [];
        foreach (self::TIPOS as $id => $tipo) {
            $tipo["id"] = $id;
            $tipos[] = $tipo;
        }
        return $tipos;
    }

    public function listarPorSimulacao($id_simulacao)
    {
        $legados = $this->repository->listarPorSimulacao($id_simulacao);
        $estimativas = $this->repository->listarEstimativasPorSimulacao($id_simulacao, self::TIPOS);
        if ($legados === false || $estimativas === false) return false;
        return array_merge($legados, $estimativas);
    }
}
