<?php

/**
 * Description of Filtros
 *
 * @author pablo
 */
class Cargos {

    private $tipo = '';
    private $sec = 0;
    private $cirnro = 0;
    private $cirlet = '';
    private $app;

    function __construct($tipo, $sec, $cirnro, $cirlet, $app) {
        $this->app = $app;
        $this->tipo = $tipo;
        $this->sec = $sec;
        $this->cirnro = $cirnro;
        $this->cirlet = $cirlet;
    }

    function getFiltros() {
        switch ($this->tipo) {
            case 'G': $sql = "SELECT distinct pspar,parnombre,pslista,nombre FROM `actapartido` WHERE psgob='R'";
                break;
            case 'D': $sql = "SELECT distinct pspar,parnombre,pslista,nombre FROM `actapartido` WHERE psdip='R'";
                break;
            case 'S': $sql = "SELECT distinct pspar,parnombre,pslista,nombre FROM `actapartido` WHERE pssen='R' and sec=" . $this->sec;
                break;
            case 'I': $sql = "SELECT distinct pspar,parnombre,pslista,nombre FROM `actapartido` WHERE pssen='R' and sec=$this->sec and cirnro=$this->cirnro and cirlet='$this->cirlet'";
                break;
            case 'C': $sql = "SELECT distinct pspar,parnombre,pslista,nombre FROM `actapartido` WHERE pssen='R' and sec=$this->sec and cirnro=$this->cirnro and cirlet='$this->cirlet'";
                break;
        }
        $disponibles = $this->app['db']->fetchAll($sql);
        $filtros = $this->app['db']->fetchAll("SELECT * FROM `filtros` WHERE tipo='$this->tipo'"
                . " and sec='$this->sec' and cirnro='$this->cirnro' and cirlet='$this->cirlet'");
        return array('disponibles' => $disponibles, 'filtros' => $filtros);
    }

    function procesar($datos) {
        $sql = "DELETE from filtros "
                . "WHERE sec=? and cirnro=? and cirlet=? and tipo=?";
        $this->app['db']->executeQuery($sql, array((int) $this->sec, (int) $this->cirnro, $this->cirlet, $this->tipo));

        foreach ($datos as $dato => $valor) {
            $dato = explode("-", $dato);
            //print_r($dato);
            $sql = "INSERT INTO filtros VALUES (?,?,?,?,?,?);";
            $this->app['db']->executeQuery($sql, array((int) $this->sec, (int) $this->cirnro, $this->cirlet, (int) $dato[0], (int) $dato[1], $this->tipo));
        }
        return "Datos modificados";
    }

    static function agregar($datos, $app) {
        $dato = explode(",", $datos);
        $sql = "INSERT INTO filtros VALUES (?,?,?,?,?,?);";
        $app['db']->executeQuery($sql, array((int) $dato[1], (int) $dato[2], $dato[3], (int) $dato[4], (int) $dato[5], $dato[0]));
        return;
    }

    static function quitar($datos, $app) {
        $dato = explode(",", $datos);
        $sql = "DELETE FROM filtros WHERE sec=? and cirnro=? and cirlet=? and pspar=? and pslista=? and tipo=? ;";
        $app['db']->executeQuery($sql, array((int) $dato[1], (int) $dato[2], $dato[3], (int) $dato[4], (int) $dato[5], $dato[0]));
        return;
    }
    
        static function agregarnacional($datos, $app) {
        $dato = explode(",", $datos);
        $sql = "INSERT INTO filtrosnacionales VALUES (?,?,?);";
        $app['db']->executeQuery($sql, array( $dato[1], $dato[2], $dato[0]));
        return;
    }

    static function quitarnacional($datos, $app) {
        $dato = explode(",", $datos);
        $sql = "DELETE FROM filtrosnacionales WHERE pspar=? and pslista=? and tipo=? ;";
        $app['db']->executeQuery($sql, array($dato[1],  $dato[2], $dato[0]));
        return;
    }

    static function getFiltrosCircuito($sec, $cirnro, $cirlet, $app) {

        $sql = "SELECT * FROM `filtros` WHERE tipo in ('G','D') 
        UNION
        SELECT * FROM `filtros` WHERE tipo in ('S') and sec=$sec
        UNION
        SELECT * FROM `filtros` WHERE tipo in ('I','C') and sec=$sec and cirnro=$cirnro and cirlet='$cirlet'";
        //echo $sql;
        $filtroscircuitos = array();
        $resultado = $app['db']->fetchAll($sql);
        foreach ($resultado as $item) {
            $filtroscircuitos[$item['sec']][$item['cirnro']][$item['cirlet']][$item['pspar']][$item['pslista']][$item['tipo']] = 1;
        }
        //print_r($filtroscircuitos);die;
        return $filtroscircuitos;
    }
  static function getFiltrosNacionales($app) {

        $sql = "SELECT * FROM filtrosnacionales";
        //echo $sql;
        $filtroscircuitos = array();
        $resultado = $app['db']->fetchAll($sql);
        foreach ($resultado as $item) {
            $filtroscircuitos[$item['pspar']][$item['pslista']][$item['tipo']] = 1;
        }
        //print_r($filtroscircuitos);die;
        return $filtroscircuitos;

    }
}
