<?php 
namespace App\Enums ; 



enum MesesEnum  : string  {
    case JANEIRO = '1' ; 
    case FEVEREIRO = '2' ;
    case MARCO = '3' ; 
    case ABRIL = '4' ; 
    case MAIO = '5' ; 
    case JUNHO = '6' ; 
    case JULHO = '7' ; 
    case AGOSTO = '8' ; 
    case SETEMBRO = '9' ; 
    case OUTUBRO = '10' ; 
    case NOVEMBRO = '11' ; 
    case DEZEMBRO = '12' ; 
    

    public function value()
    {
        return static::getValue($this);
    }

    public static function getValue($enum)
    {
        return constant("self::{$enum}")->value;

    }
    public function label(): string {
        return static::getLabel($this);
    }
    protected static function getLabel($enum){
        return match ($enum) {
            self::JANEIRO => 'Janeiro',
            self::FEVEREIRO => 'Fevereiro',
            self::MARCO => 'Março',
            self::ABRIL => 'Abril',
            self::MAIO => 'Maio',
            self::JUNHO => 'Junho',
            self::JULHO => 'Julho',
            self::AGOSTO => 'Agosto',
            self::SETEMBRO => 'Setembro',
            self::OUTUBRO => 'Outubro',
            self::NOVEMBRO => 'Novembro',
            self::DEZEMBRO => 'Dezembro',
        };
    }

}
