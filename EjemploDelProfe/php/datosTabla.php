<?php
    $vPersonas = array(
        array(
            'id'=>3,
            'documento' => '22333444',
            'nombre' => 'Toto',
            'apellido' => 'Toto'
        ),
        array(
            'id'=>4,
            'documento' => '23232332',
            'nombre' => 'Tata',
            'apellido' => 'Tata'
        )
    );

    $filasTabla="";
    foreach ( $vPersonas as $cadaPersona ) {
        $filasTabla .= "<tr>";
            $filasTabla .= "<th scope='row'>" . $cadaPersona['id'] . "</th>";
            $filasTabla .= "<td>" . $cadaPersona['documento'] . "</td>";
            $filasTabla .= "<td>" . $cadaPersona['nombre'] . "</td>";
            $filasTabla .= "<td>" . $cadaPersona['apellido'] . "</td>";
        $filasTabla .= "</tr>";
    }
        

?>