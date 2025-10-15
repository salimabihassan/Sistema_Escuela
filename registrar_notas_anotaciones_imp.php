<?php

include ("conexiones.php");
header('Content-Type: text/html; charset=ISO-8859-1');
error_reporting(0);
$conexion=conectarse_escuela();


$ci_alu=$_POST['ci_alu'];
$ci_alu2=$_POST['ci_alu2'];
$nom_alu=$_POST['nom_alu'];
$grado=$_POST['grado'];


$periodo=$_POST['periodo'];
$guardar=$_POST['guardar'];

$prom_r=$_POST['prom_r'];
$prom_sem=$_POST['prom_sem'];
$prom_sem2=$_POST['prom_sem2'];
$prom_gen=$_POST['prom_gen'];
$anota_n=$_POST['anota_n'];
$anota_p=$_POST['anota_p'];
$porc_asist=$_POST['porc_asist'];
$obs=$_POST['obs'];

$fecha_insc=date("d-m-Y");
$btnaccion=$_POST['btnaccion'];

$total_sql=$_POST['total_sql'];
$total_consultar=$_POST['total_consultar'];
$total_consultar2=$_POST['total_consultar2'];
$total_periodo=$_POST['total_periodo'];

	$total = "";
for ($segundos = 1; $segundos <= 3; $segundos++)
{

//Para cada iteración 1 segundo
sleep($segundos);
$total = $segundos;
}


$consulta_imp="SELECT * FROM imp WHERE id='1'";
     $resultado_imp=mysql_query($consulta_imp,$conexion);
	 $datos_imp=mysql_fetch_array($resultado_imp);
	  $ci_alu=$datos_imp['ci_alu'];
	  $ci_alu2=$datos_imp['ci_alu2'];
	$ci_alu2=strtoupper($ci_alu2);
		$obs=strtoupper($obs);
	
	
	 
		
		//3. se establece la intrucción sql
		  $prom_gen=0;
	 $consulta_consultar="SELECT * FROM alumno WHERE ci_alu='$ci_alu' and ci_alu2='$ci_alu2'";
     $resultado_consultar=mysql_query($consulta_consultar,$conexion);
	 $total_consultar=mysql_num_rows($resultado_consultar);
	  
	   if ($total_consultar>0){
		 $datos_consultar=mysql_fetch_array($resultado_consultar);
		 $borrado=$datos_consultar['borrado'];
		
			 
		   if ($borrado==0){			 			  
			 $nom_alu=$datos_consultar['nom_alu'];
			 $grado=$datos_consultar['grado'];		     
			 $periodo=$datos_consultar['periodo'];
			 $id_alu=$datos_consultar['id_alu'];
			 $borrado=$datos_consultar['borrado'];
			 
			 $cod_curso=$grado.$periodo;
			 $consulta_curso="SELECT * FROM curso WHERE cod_curso='$cod_curso'";
     $resultado_curso=mysql_query($consulta_curso,$conexion);
	 $datos_curso=mysql_fetch_array($resultado_curso);
	 $ci_prof=$datos_curso['ci_prof'];
	 $ci_prof2=$datos_curso['ci_prof2'];
	
	  
	 $consulta_prof="SELECT * FROM prof WHERE ci_prof='$ci_prof' and ci_prof2='$ci_prof2'";
     $resultado_prof=mysql_query($consulta_prof,$conexion);
	 $datos_prof=mysql_fetch_array($resultado_prof);
	 $nom_prof=$datos_prof['nom_prof'];
	 
	 $consulta_dir="SELECT * FROM prof WHERE id_prof='1'";
     $resultado_dir=mysql_query($consulta_dir,$conexion);
	 $datos_dir=mysql_fetch_array($resultado_dir);
	 $nom_dir=$datos_dir['nom_prof'];
	 
			 if($grado<3){	$consulta_sql2="SELECT *  FROM asignatura WHERE g1='SI' and borrado=0 ORDER BY cod_asig" ;}
			 if(($grado>2)and($grado<7)){$consulta_sql2="SELECT *  FROM asignatura WHERE g3='SI' and borrado=0 ORDER BY cod_asig" ;}
			 if($grado>6){$consulta_sql2="SELECT *  FROM asignatura WHERE g7='SI' and borrado=0 ORDER BY cod_asig" ;}
			 $resultado_sql2=mysql_query($consulta_sql2,$conexion);
			 $total_sql2=mysql_num_rows($resultado_sql2);
			 
			 if ($total_sql2>0){ 
		    	 		 	
       			 
		    	 $t=0;
				 $prom_sem=0;			
			     while ($datos_sql2=mysql_fetch_array($resultado_sql2))
			      {$i=0;
				  $t++;
				   	$cod_asig[$t]=$datos_sql2['cod_asig'];
				  	
					
					$consulta_periodo2="SELECT * FROM notas$periodo WHERE ci_alu='$ci_alu' and ci_alu2='$ci_alu2' and periodo='$periodo' and semestre='1'
					 and cod_asig='$cod_asig[$t]' order by id_notas";
					 $resultado_periodo2=mysql_query($consulta_periodo2,$conexion);
					 $total_periodo2=mysql_num_rows($resultado_periodo2);
					 if ($total_periodo2>0){ 
					 while ($datos_periodo2=mysql_fetch_array($resultado_periodo2))
			      {//echo '-'.$t.$i;
			 
					
					$id_notas[$t][$i]=$datos_periodo2['id_notas'];
					$nota[$t][$i]=$datos_periodo2['nota'];
					$prom[$t]=$datos_periodo2['nota_prom'];
					
					$anota_n=$datos_periodo2['anot_n'];
					if($anota_n<1){$anota_n="";}
					$anota_p=$datos_periodo2['anot_p'];
					if($anota_p<1){$anota_p="";}
					$porc_asist=$datos_periodo2['porc_asist'];
					if($porc_asist<1){$porc_asist="";}
					$obs=$datos_periodo2['obs'];
					$id=$id_notas[$t][$i];
					
					$consulta_imp2="update notas$periodo set final='1' where id_notas='$id'";
					$resultado_imp2= mysql_query($consulta_imp2,$conexion);
					$tota_imp2= mysql_affected_rows($conexion);
					
					if ($nota[$t][$i]<1){$nota[$t][$i]="";}else{$nota[$t][$i]=number_format($nota[$t][$i], 1, '.', '');}
					if ($prom[$t]<1){$prom[$t]="";}else{$prom[$t]=number_format($prom[$t], 1, '.', '');} 
					$i++;
					}}
					
						if ($cod_asig[$t]<100){if($prom[$t]>0){ $s++;$s;}
						$prom_sem+=$prom[$t];
						}
					}
					
					 
						$cod_asig[$t];
						if ($cod_asig[$t]==100){
						if(($prom[$t]>=1)and($prom[$t]<=3.9)){$prom[$t]='I';}
						if(($prom[$t]>=4)and($prom[$t]<=4.9)){$prom[$t]='S';}
						if(($prom[$t]>=5)and($prom[$t]<=5.9)){$prom[$t]='B';}
						if(($prom[$t]>=6)and($prom[$t]<=7)){$prom[$t]='MB';}} 
						$t=$t-1;
			$prom_sem=$prom_sem/$s;
			if ($prom_sem<1){$prom_sem="";}else{$prom_sem=number_format($prom_sem, 1, '.', '');}
			
			
			}else{ ?>
            <script>
             alert('Este Alumno no tiene Registrada notas en el Primer Semestre...!!!');
            </script>
            
            <?php
			}if($grado<3){	$consulta_sql2="SELECT *  FROM asignatura WHERE g1='SI' and borrado=0 ORDER BY cod_asig" ;}
			 if(($grado>2)and($grado<7)){$consulta_sql2="SELECT *  FROM asignatura WHERE g3='SI' and borrado=0 ORDER BY cod_asig" ;}
			 if($grado>6){$consulta_sql2="SELECT *  FROM asignatura WHERE g7='SI' and borrado=0 ORDER BY cod_asig" ;}
			 $resultado_sql2=mysql_query($consulta_sql2,$conexion);
			 $total_sql2=mysql_num_rows($resultado_sql2);
			 
			 if ($total_sql2>0){ 
		    	 		 	
       			 $s=0;
		    	 $t=0;
				 $prom_sem2=0;	
				 $g=0;		
			     while ($datos_sql2=mysql_fetch_array($resultado_sql2))
			      {$i=0;
				  $t++;
				   	$cod_asig[$t]=$datos_sql2['cod_asig'];
				  	
					
					$consulta_periodo2="SELECT * FROM notas$periodo WHERE ci_alu='$ci_alu' and ci_alu2='$ci_alu2' and periodo='$periodo' and semestre='2'
					 and cod_asig='$cod_asig[$t]' order by id_notas";
					 $resultado_periodo2=mysql_query($consulta_periodo2,$conexion);
					 $total_periodo2=mysql_num_rows($resultado_periodo2);
					 if ($total_periodo2>0){ 
					 while ($datos_periodo2=mysql_fetch_array($resultado_periodo2))
			      {
					$id_notas2[$t][$i]=$datos_periodo2['id_notas'];
					$mota[$t][$i]=$datos_periodo2['nota'];
					$prom2[$t]=$datos_periodo2['nota_prom'];
					
					$id=$id_notas2[$t][$i];
					
					$consulta_imp3="update notas$periodo set final='1' where id_notas='$id'";
					$resultado_imp3= mysql_query($consulta_imp3,$conexion);
					$tota_imp3= mysql_affected_rows($conexion);
					
					if ($mota[$t][$i]<1){$mota[$t][$i]="";}else{$mota[$t][$i]=number_format($mota[$t][$i], 1, '.', '');}
					if ($prom2[$t]<1){$prom2[$t]="";}else{$prom2[$t]=number_format($prom2[$t], 1, '.', '');} 
					$i++;
				  }}
					
					
						if ($cod_asig[$t]<100){if($prom2[$t]>0){ $s++;$s;}
						$prom_sem2+=$prom2[$t];
						$prom_final[$t]=$prom[$t]+$prom2[$t];
						
						if ($prom2[$t]>1){
							
							$prom_final[$t]=(($prom_final[$t])/2);
						}
						if($prom_final[$t]>0){ $g++;}
						$prom_gen+=$prom_final[$t];
						if ($prom_final[$t]<1){$prom_final[$t]="";}else{$prom_final[$t]=number_format($prom_final[$t], 1, '.', '');} 
						
						}			
						if (($prom2[$t]<1)and($cod_asig[$t]==100)){
							$prom_final[$t]=$prom[$t];
							
						}
			}
			if ($cod_asig[$t]==100){
						if(($prom2[$t]>=1)and($prom2[$t]<=3.9)){$prom2[$t]='I';}
						if(($prom2[$t]>=4)and($prom2[$t]<=4.9)){$prom2[$t]='S';}
						if(($prom2[$t]>=5)and($prom2[$t]<=5.9)){$prom2[$t]='B';}
						if(($prom2[$t]>=6)and($prom2[$t]<=7)){$prom2[$t]='MB';}} 
			$t=$t-1;
			$prom_sem2=$prom_sem2/$s;
			$prom_gen=$prom_gen/$g;
			$prom_gen=number_format($prom_gen, 1, '.', '');
			if ($prom_sem2<1){$prom_sem2="";}else{$prom_sem2=number_format($prom_sem2, 1, '.', '');}
			$prom_gen=round($prom_gen, 2, PHP_ROUND_HALF_DOWN);
			$prom_gen=substr($prom_gen,0,3); 
			
		    }else{ ?>
            <script>
             alert('Este Alumno no tiene Registrada notas en el Segundo Semestre...!!!');
            </script>
            
            <?php
			}
		    }else{ ?>
            <script>
             alert('Esta Cedula del Alumno fue eliminado de la Base de Datos, Para Recuperarlo Vaya a la Papelera de Reciclaje...!!!');
            </script>
            
            <?php
			 $nom_alu="";
			 $sexo="";
			 $id_alu="";
			 $grado="";
			 
			 $periodo="";			
			 $button="";
			break;}
		
		}else{?>
            <script>
             alert('Esta Cedula de Alumno no esta registrada en el sistema...!!!');
            </script>
            
            <?php $nom_alu="";
			 $sexo="";
			 $id_alu="";
			 $grado="";
			 
			 $periodo="";			
			 $button="";}
			
			
$fecha=$fecha_insc;
			 
			 function obtenerFechaEnLetra($fecha){
    $dia= conocerDiaSemanaFecha($fecha);
    $num = date("j", strtotime($fecha));
    $anno = date("Y", strtotime($fecha));
    $mes = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');
    $mes = $mes[(date('m', strtotime($fecha))*1)-1];
    return $dia.', '.$num.' de '.$mes.' del '.$anno;
}
 
function conocerDiaSemanaFecha($fecha) {
    $dias = array('Domingo', 'Lunes', 'Martes', 'Mi&eacute;rcoles', 'Jueves', 'Viernes', 'Sábado');
    $dia = $dias[date('w', strtotime($fecha))];
    return $dia;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<title>INFORME DE NOTAS FINAL</title>
<style type="text/css">
#apDiv1 {
	position:absolute;
	width:956px;
	height:302px;
	z-index:1;
	left: 169px;
	top: 180px;
}
#apDiv2 {
	position:absolute;
	width:116px;
	height:47px;
	z-index:2;
	left: 201px;
	top: 182px;
}
body,td,th {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;
}
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	font-size: 18px;
}
a:link {
	text-decoration: none;
}
a:visited {
	text-decoration: none;
}
a:hover {
	text-decoration: none;
}
a:active {
	text-decoration: none;
}

.sombra{ 
		text-shadow: 0.05em 0.05em 0.03em  #000;
	}
	
.caja { 
    -webkit-box-shadow: 5px 5px 5px rgba(0,0,0,.5);
    -moz-box-shadow: 5px 5px 4px rgba(0,0,0,.5);
    box-shadow: 5px 5px 5px rgba(0,0,0,.5);
    padding: 5px;
    background:#F3F3F3 ;
	margin:5px;
	width:1024px;
		
}	



</style>
</head>

<body>
<center>
 
 <form id="form1" name="form1" method="post" action="registrar_notas1_anotaciones.php">
   <table width="91%" height="102" border="0" cellpadding="2" cellspacing="2">
     <tr>
       <td width="178" rowspan="5" align="center" valign="middle"><img src="/www/sistema/imagenes/logo altas cumbres.jpg" width="178" height="89" /></td>
       <td width="546" height="18" align="left" valign="middle"><strong>Escuela B&aacute;sica Particular N&ordm; 2271</strong></td>
       <td width="259" align="right" valign="middle">&nbsp;</td>
     </tr>
     <tr>
       <td height="18" align="left"><strong>Colegio &quot;Altas Cumbres del Rosal&quot;</strong></td>
       <td width="259" align="right" valign="middle">&nbsp;</td>
     </tr>
     <tr>
       <td height="18" align="left"><strong>Fono: 316 55 18</strong></td>
       <td width="259" align="right" valign="middle">&nbsp;</td>
     </tr>
     <tr>
       <td height="18" align="left"><strong>caltascumbres@gmail.com</strong></td>
       <td width="259" align="right" valign="middle">&nbsp;</td>
     </tr>
     <tr>
       <td height="18" align="left" valign="top"><strong>RBD: 26392-3</strong></td>
       <th width="259" align="left" valign="middle"><?php echo obtenerFechaEnLetra($fecha);?>
        <input name="fecha_insc2" type="hidden" id="fecha_insc2" value="<?php echo $fecha_insc;?>" maxlength="150" size="20" /></th>
     </tr>
   </table>
   <table width="926" border="0" align="center" cellpadding="2" cellspacing="2">
      <tr align="left">
        <td height="26" colspan="4" align="center" bgcolor="#E9E9E9"><font class="sombra" color="#00366C" face="Arial Black, Gadget, sans-serif" size="5">INFORME DE NOTAS FINAL</font></td>
      </tr>
      <tr align="left">
        <td height="18" colspan="4" align="center" bgcolor="#FFFFFF"><strong>DATOS DEL ALUMNO:</strong></td>
      </tr>
    <tr align="left">
      <th width="211" height="22" align="center" bgcolor="#E9E9E9"><h4>C&eacute;dula:  <?php $ci_alu=number_format($ci_alu, 0, ' ', '.'); echo $ci_alu.'-'.$ci_alu2;?></h4></th>
      <th width="334" align="center" bgcolor="#E9E9E9"><h4>Nombre: <?php echo $nom_alu;?></h4></th>
      <th width="130" height="22" align="center" bgcolor="#E9E9E9"><h4>Curso:
<?php  if ($grado=='1'){echo '1ro';}
	  																			if ($grado=='2'){echo '2do';}
																				if ($grado=='3'){echo '3ro';}
																				if ($grado=='4'){echo '4to';}
																				if ($grado=='5'){echo '5to';}
																				if ($grado=='6'){echo '6to';}
																				if ($grado=='7'){echo '7mo';}
																				if ($grado=='8'){echo '8vo';}?>
      </h4></th>
      <th width="225" height="22" align="center" bgcolor="#E9E9E9"><h4>Periodo Escolar: <?php echo $periodo;?></h4></th>
      </tr>
    
  </table>
    
    
    <p>
      <?php  if ($total_consultar>0 ){ ?>
      
      <?php
	  
	
	  if($grado<3){
	$consulta_sql="SELECT *  FROM asignatura WHERE g1='SI' and borrado=0 ORDER BY cod_asig" ;
    $resultado_sql=mysql_query($consulta_sql,$conexion);
	 $total_sql=mysql_num_rows($resultado_sql);
	 
	  }  if(($grado>2)and($grado<7)){
	$consulta_sql="SELECT *  FROM asignatura WHERE g3='SI' and borrado=0 ORDER BY cod_asig" ;
    $resultado_sql=mysql_query($consulta_sql,$conexion);
	 $total_sql=mysql_num_rows($resultado_sql);
	  }
	   if($grado>6){
	$consulta_sql="SELECT *  FROM asignatura WHERE g7='SI' and borrado=0 ORDER BY cod_asig" ;
    $resultado_sql=mysql_query($consulta_sql,$conexion);
	 $total_sql=mysql_num_rows($resultado_sql);
	  }?>
       
    </p>
    <table width="78%" border="0" align="center" cellpadding="2" cellspacing="2">
      <tr>
     <td height="26" colspan="2" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Nro.</strong></td>
     <td colspan="3" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Asignatura</strong></td>
     <td colspan="10" align="center" bgcolor="#E9E9E9"><strong>Notas Primer Semestre</strong></td>
     <td width="35" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Prom</strong></td>
     <td width="1" rowspan="2" align="center" bgcolor="#FFFFFF">&nbsp;</td>
     <td colspan="10" align="center" bgcolor="#E9E9E9"><strong>Notas Segundo Semestre</strong></td>
     <td width="35" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Prom</strong></td>
     <td width="1" rowspan="2" align="center" bgcolor="#FFFFFF">&nbsp;</td>
     <td width="39" rowspan="2" align="center" bgcolor="#E9E9E9"><strong>Prom Final</strong></td>
	  </tr>
      <tr>
        <td align="center" bgcolor="#E9E9E9"><strong>0</strong></td>
        <td align="center" bgcolor="#E9E9E9"><strong>1</strong></td>
        <td align="center" bgcolor="#E9E9E9"><strong>2</strong></td>
        <td align="center" bgcolor="#E9E9E9"><strong>3</strong></td>
        <td align="center" bgcolor="#E9E9E9"><strong>4</strong></td>
        <td align="center" bgcolor="#E9E9E9"><strong>5</strong></td>
        <td align="center" bgcolor="#E9E9E9"><strong>6</strong></td>
        <td align="center" bgcolor="#E9E9E9"><strong>7</strong></td>
        <td align="center" bgcolor="#E9E9E9"><strong>8</strong></td>
        <td align="center" bgcolor="#E9E9E9"><strong>9</strong></td>
        <td width="17" align="center" bgcolor="#E9E9E9"><strong>0</strong></td>
        <td width="17" align="center" bgcolor="#E9E9E9"><strong>1</strong></td>
        <td width="17" align="center" bgcolor="#E9E9E9"><strong>2</strong></td>
        <td width="17" align="center" bgcolor="#E9E9E9"><strong>3</strong></td>
        <td width="17" align="center" bgcolor="#E9E9E9"><strong>4</strong></td>
        <td width="17" align="center" bgcolor="#E9E9E9"><strong>5</strong></td>
        <td width="17" align="center" bgcolor="#E9E9E9"><strong>6</strong></td>
        <td width="17" align="center" bgcolor="#E9E9E9"><strong>7</strong></td>
        <td width="17" align="center" bgcolor="#E9E9E9"><strong>8</strong></td>
        <td width="17" align="center" bgcolor="#E9E9E9"><strong>9</strong></td>
      </tr>
	 <?php
	$i=0;
	$t=1;
	$x=0;
	$fila=1; 
	while ($datos_sql=mysql_fetch_array($resultado_sql))
	{
	$resto=$fila % 2;
		  if ($resto==0)  $color ="#FFFFFF";   else    $color ="#CCEEFF"; 
		  $fila++;
	$x++;
	
	
	?>
    
    <tr bgcolor="<?php echo $color; ?>">
    
    
    <td colspan="2" align="center" bgcolor="<?php echo $color; ?>">
      <?php echo $x?></td>
    <td colspan="3" align="left" nowrap="nowrap" bgcolor="<?php echo $color; ?>"><?php echo $datos_sql['cod_asig'].'.-'.$datos_sql['nom_asig'];//$nota[$t][$i]=$t.$i;?>	      <input name="txtnom_asig<?php echo $t; ?>" type="hidden" value="<?php echo $txtnom_asig[$t]=$datos_sql['nom_asig']?>" /><input name="txtcod_asig<?php echo $t; ?>" type="hidden" value="<?php echo $txtcod_asig[$t]=$datos_sql['cod_asig']?>" />
      <input name="semestre" type="hidden" id="semestre" value="<?php echo $semestre;?>" />
      <input name="total_periodo" type="hidden" id="total_periodo" value="<?php echo $total_periodo;?>" /></td>
		<td width="25" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><?php echo $nota[$t][$i];?>
	      <input name="id_notas<?php echo $t.$i; ?>" type="hidden" id="id_notas<?php echo $t.$i; ?>" value="<?php echo $id_notas[$t][$i];?>" />
        <input name="nota<?php echo $t.$i; ?>" type="hidden" id="nota<?php echo $t.$i; ?>" value="<?php echo $nota[$t][$i];?>" /></td><?php $i++;// $nota[$t][$i]=$t.$i; ?>
        <td width="38" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><?php echo $nota[$t][$i];?>
          <input name="id_notas<?php echo $t.$i; ?>" type="hidden" id="id_notas<?php echo $t.$i; ?>" value="<?php echo $id_notas[$t][$i];?>" />
        <input name="nota<?php echo $t.$i; ?>" type="hidden" id="nota<?php echo $t.$i; ?>" value="<?php echo $nota[$t][$i];?>" /></td><?php $i++;//$nota[$t][$i]=$t.$i; ?>
        <td width="32" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><?php echo $nota[$t][$i];?>
          <input name="id_notas<?php echo $t.$i; ?>" type="hidden" id="id_notas<?php echo $t.$i; ?>" value="<?php echo $id_notas[$t][$i];?>" />
        <input name="nota<?php echo $t.$i; ?>" type="hidden" id="nota<?php echo $t.$i; ?>" value="<?php echo $nota[$t][$i];?>" /></td><?php $i++;//$nota[$t][$i]=$t.$i; ?>
        <td width="17" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><?php echo $nota[$t][$i];?>
          <input name="id_notas<?php echo $t.$i; ?>" type="hidden" id="id_notas<?php echo $t.$i; ?>" value="<?php echo $id_notas[$t][$i];?>" />
<input name="nota<?php echo $t.$i; ?>" type="hidden" id="nota<?php echo $t.$i; ?>" value="<?php echo $nota[$t][$i];?>" /></td><?php $i++;//$nota[$t][$i]=$t.$i; ?>
        <td width="17" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><?php echo $nota[$t][$i];?>
          <input name="id_notas<?php echo $t.$i; ?>" type="hidden" id="id_notas<?php echo $t.$i; ?>" value="<?php echo $id_notas[$t][$i];?>" />
<input name="nota<?php echo $t.$i; ?>" type="hidden" id="nota<?php echo $t.$i; ?>" value="<?php echo $nota[$t][$i];?>" /></td><?php $i++;//$nota[$t][$i]=$t.$i; ?>
        <td width="17" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><?php echo $nota[$t][$i];?>
          <input name="id_notas<?php echo $t.$i; ?>" type="hidden" id="id_notas<?php echo $t.$i; ?>" value="<?php echo $id_notas[$t][$i];?>" />
<input name="nota<?php echo $t.$i; ?>" type="hidden" id="nota<?php echo $t.$i; ?>" value="<?php echo $nota[$t][$i];?>" /></td><?php $i++;//$nota[$t][$i]=$t.$i; ?>
        <td width="17" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><?php echo $nota[$t][$i];?>
          <input name="id_notas<?php echo $t.$i; ?>" type="hidden" id="id_notas<?php echo $t.$i; ?>" value="<?php echo $id_notas[$t][$i];?>" />
<input name="nota<?php echo $t.$i; ?>" type="hidden" id="nota<?php echo $t.$i; ?>" value="<?php echo $nota[$t][$i];?>" /></td><?php $i++;//$nota[$t][$i]=$t.$i; ?>
        <td width="17" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><?php echo $nota[$t][$i];?>
          <input name="id_notas<?php echo $t.$i; ?>" type="hidden" id="id_notas<?php echo $t.$i; ?>" value="<?php echo $id_notas[$t][$i];?>" />
<input name="nota<?php echo $t.$i; ?>" type="hidden" id="nota<?php echo $t.$i; ?>" value="<?php echo $nota[$t][$i];?>" /></td><?php $i++; //$nota[$t][$i]=$t.$i;?>
        <td width="17" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><?php echo $nota[$t][$i];?>
          <input name="id_notas<?php echo $t.$i; ?>" type="hidden" id="id_notas<?php echo $t.$i; ?>" value="<?php echo $id_notas[$t][$i];?>" />
<input name="nota<?php echo $t.$i; ?>" type="hidden" id="nota<?php echo $t.$i; ?>" value="<?php echo $nota[$t][$i];?>" /></td><?php $i++;// $nota[$t][$i]=$t.$i;?>
        <td width="17" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><?php echo $nota[$t][$i];?>
          <input name="id_notas<?php echo $t.$i; ?>" type="hidden" id="id_notas<?php echo $t.$i; ?>" value="<?php echo $id_notas[$t][$i];?>" />
<input name="nota<?php echo $t.$i; ?>" type="hidden" id="nota<?php echo $t.$i; ?>" value="<?php echo $nota[$t][$i];?>" /></td><?php $i++;// $nota[$t][$i]=$t.$i; ?>
		<td align="center" valign="middle" bgcolor="<?php echo $color; ?>"><strong><?php echo $prom[$t];?>
		  <input name="prom<?php echo $t; ?>" type="hidden" id="prom<?php echo $t; ?>" value="<?php echo $prom[$t];?>" />
		</strong></td>
        
        <?php $i=0;?>
		<td align="center" bgcolor="#FFFFFF">&nbsp;</td>
		<td align="center" valign="middle" bgcolor="<?php echo $color; ?>"><input name="id_notas2<?php echo $t.$i; ?>" type="hidden" id="id_notas2<?php echo $t.$i; ?>" value="<?php echo $id_notas2[$t][$i];?>" />
		  <input name="mota<?php echo $t.$i; ?>" type="hidden" id="mota<?php echo $t.$i; ?>" value="<?php echo $mota[$t][$i];?>" />
	    <?php echo $mota[$t][$i];$i++;?></td>
		<td align="center" valign="middle" bgcolor="<?php echo $color; ?>"><input name="id_notas2<?php echo $t.$i; ?>" type="hidden" id="id_notas2<?php echo $t.$i; ?>" value="<?php echo $id_notas2[$t][$i];?>" />
		  <input name="mota<?php echo $t.$i; ?>" type="hidden" id="mota<?php echo $t.$i; ?>" value="<?php echo $mota[$t][$i];?>" />
	    <?php echo $mota[$t][$i];$i++;?></td>
		<td align="center" valign="middle" bgcolor="<?php echo $color; ?>"><input name="id_notas2<?php echo $t.$i; ?>" type="hidden" id="id_notas2<?php echo $t.$i; ?>" value="<?php echo $id_notas2[$t][$i];?>" />
		  <input name="mota<?php echo $t.$i; ?>" type="hidden" id="mota<?php echo $t.$i; ?>" value="<?php echo $mota[$t][$i];?>" />
	    <?php echo $mota[$t][$i];$i++;?></td>
		<td align="center" valign="middle" bgcolor="<?php echo $color; ?>"><input name="id_notas2<?php echo $t.$i; ?>" type="hidden" id="id_notas2<?php echo $t.$i; ?>" value="<?php echo $id_notas2[$t][$i];?>" />
		  <input name="mota<?php echo $t.$i; ?>" type="hidden" id="mota<?php echo $t.$i; ?>" value="<?php echo $mota[$t][$i];?>" />
	    <?php echo $mota[$t][$i];$i++;?></td>
		<td align="center" valign="middle" bgcolor="<?php echo $color; ?>"><input name="id_notas2<?php echo $t.$i; ?>" type="hidden" id="id_notas2<?php echo $t.$i; ?>" value="<?php echo $id_notas2[$t][$i];?>" />
		  <input name="mota<?php echo $t.$i; ?>" type="hidden" id="mota<?php echo $t.$i; ?>" value="<?php echo $mota[$t][$i];?>" />
	    <?php echo $mota[$t][$i];$i++;?></td>
		<td align="center" valign="middle" bgcolor="<?php echo $color; ?>"><input name="id_notas2<?php echo $t.$i; ?>" type="hidden" id="id_notas2<?php echo $t.$i; ?>" value="<?php echo $id_notas2[$t][$i];?>" />
		  <input name="mota<?php echo $t.$i; ?>" type="hidden" id="mota<?php echo $t.$i; ?>" value="<?php echo $mota[$t][$i];?>" />
	    <?php echo $mota[$t][$i];$i++;?></td>
		<td align="center" valign="middle" bgcolor="<?php echo $color; ?>"><input name="id_notas2<?php echo $t.$i; ?>" type="hidden" id="id_notas2<?php echo $t.$i; ?>" value="<?php echo $id_notas2[$t][$i];?>" />
		  <input name="mota<?php echo $t.$i; ?>" type="hidden" id="mota<?php echo $t.$i; ?>" value="<?php echo $mota[$t][$i];?>" />
	    <?php echo $mota[$t][$i];$i++;?></td>
		<td align="center" valign="middle" bgcolor="<?php echo $color; ?>"><input name="id_notas2<?php echo $t.$i; ?>" type="hidden" id="id_notas2<?php echo $t.$i; ?>" value="<?php echo $id_notas2[$t][$i];?>" />
		  <input name="mota<?php echo $t.$i; ?>" type="hidden" id="mota<?php echo $t.$i; ?>" value="<?php echo $mota[$t][$i];?>" />
	    <?php echo $mota[$t][$i];$i++;?></td>
		<td align="center" valign="middle" bgcolor="<?php echo $color; ?>"><input name="id_notas2<?php echo $t.$i; ?>" type="hidden" id="id_notas2<?php echo $t.$i; ?>" value="<?php echo $id_notas2[$t][$i];?>" />
		  <input name="mota<?php echo $t.$i; ?>" type="hidden" id="mota<?php echo $t.$i; ?>" value="<?php echo $mota[$t][$i];?>" />
	    <?php echo $mota[$t][$i];$i++;?></td>
		<td align="center" valign="middle" bgcolor="<?php echo $color; ?>"><input name="id_notas2<?php echo $t.$i; ?>" type="hidden" id="id_notas2<?php echo $t.$i; ?>" value="<?php echo $id_notas2[$t][$i];?>" />
		  <input name="mota<?php echo $t.$i; ?>" type="hidden" id="mota<?php echo $t.$i; ?>" value="<?php echo $mota[$t][$i];?>" />
	    <?php echo $mota[$t][$i];$i++;?></td>
		<td width="35" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><strong>
		  <input name="prom2<?php echo $t; ?>" type="hidden" id="prom2<?php echo $t; ?>" value="<?php echo $prom2[$t];?>" />
	    <?php echo $prom2[$t];$i++;?></strong></td>
		<td width="1" align="center" valign="middle" bgcolor="#FFFFFF">&nbsp;</td>
		<td width="39" align="center" valign="middle" bgcolor="<?php echo $color; ?>"><strong><?php echo $prom_final[$t];$i++;?>
	    <input name="prom_final<?php echo $t; ?>" type="hidden" id="prom_final<?php echo $t; ?>" value="<?php echo $prom_final[$t];?>" />
		</strong></td>
		<?php // echo 'prom'.$t.'='.$prom[$t];
if ($i=9){
	$t++;
	$i=0;
	}}?>


    <tr>
     <td height="12" colspan="5" align="center" bgcolor="#FFFFFF"><input name="total_consultar" type="hidden" id="total_consultar" value="<?php echo $total_consultar; ?>" />       <input name="total_sql" type="hidden" value="<?php echo $total_sql; ?>" />
       <input name="prom_r" type="hidden" id="prom_r" value="<?php echo $prom_r; ?>" /></td>
     <td height="12" align="center" bgcolor="#FFFFFF">&nbsp;</td>
     <td height="12" align="center" bgcolor="#FFFFFF">&nbsp;</td>
     <td height="12" align="center" bgcolor="#FFFFFF">&nbsp;</td>
     <td height="12" align="right" bgcolor="#FFFFFF">&nbsp;</td>
     <td height="12" colspan="6" align="right" bgcolor="#E9E9E9">Promedio 1er Semestre</td>
     <td height="12" align="center" bgcolor="#CCFFFF"><strong><?php echo $prom_sem;?>       
       <input name="prom_sem" type="hidden" id="prom_sem" value="<?php echo $prom_sem;?>" />
     </strong></td>
     <td height="12" align="center" bgcolor="#FFFFFF">&nbsp;</td>
     <td align="center" bgcolor="#FFFFFF">&nbsp;</td>
     <td align="center" bgcolor="#FFFFFF">&nbsp;</td>
     <td align="center" bgcolor="#FFFFFF">&nbsp;</td>
     <td align="right" bgcolor="#FFFFFF">&nbsp;</td>
     <td colspan="6" align="right" bgcolor="#E9E9E9">Promedio 2do Semestre</td>
     <td height="12" align="center" bgcolor="#CCFFFF"><strong><?php echo $prom_sem2;?>
       <input name="prom_sem2" type="hidden" id="prom_sem2" value="<?php echo $prom_sem2;?>" />
     </strong></td>
     <td height="12" align="center" bgcolor="#FFFFFF">&nbsp;</td>
     <td height="12" align="center" bgcolor="#FFFFFF">&nbsp;</td>
     </tr>
    <tr>
      <td height="0" colspan="2" align="center" bgcolor="#E9E9E9"><strong>Anotaciones</strong></td>
      <td height="0" colspan="3" align="center" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="0" align="left" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="0" align="left" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="0" align="left" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="0" align="left" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="0" align="left" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="0" align="left" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="0" align="left" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="0" align="left" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="0" align="left" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="0" align="left" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="-3" align="center" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="-3" align="center" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="-3" align="center" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="-3" align="center" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="-3" align="center" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="-3" align="center" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="-3" align="center" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="-3" align="center" bgcolor="#FFFFFF">&nbsp;</td>
      <td height="-3" colspan="6" align="center" bgcolor="#E9E9E9"><h4><strong>Promedio General</strong></h4></td>
      <td height="-3" align="center" bgcolor="#CCFFFF"><h3><strong>         <input name="prom_gen" type="hidden" id="prom_gen" value="<?php echo $prom_gen;?>" />         <?php echo $prom_gen;?></strong></h3></td>
    </tr>
    <tr>
      <th width="66" height="2" align="left" bgcolor="#E9E9E9">Negativas</th>
      <th width="17" align="left" bgcolor="#E9E9E9"><?php echo $anota_n;?></th>
      <th width="76" rowspan="2" align="right" bgcolor="#E9E9E9">Porcentaje Asistencia</th>
      <th width="23" rowspan="2" align="left" bgcolor="#E9E9E9"><?php echo $porc_asist;?>%</th>
      <th width="1" rowspan="2" align="left" bgcolor="#FFFFFF">&nbsp;</th>
      <td colspan="3" rowspan="2" align="right" bgcolor="#FFFFFF"><strong>Observaciones:
        
      </strong></td>
      <td colspan="22" rowspan="2" align="left" bgcolor="#FFFFFF"><strong><?php echo $obs;?></strong></td>
      </tr>
    <tr>
      <th height="0" align="left" bgcolor="#E9E9E9">Positivas</th>
      <th height="0" align="left" bgcolor="#E9E9E9"><?php echo $anota_p;?></th>
      </tr>

    
    </table>
    <p>
      <?php } ?>
    </p>
    <p>&nbsp;</p>
    <table width="763" border="0" cellpadding="2" cellspacing="2">
      <tr>
        <th align="center">______________________________</th>
        <th>&nbsp;</th>
        <th align="center">______________________________</th>
      </tr>
      <tr>
        <th align="center"><?php echo $nom_prof;?></th>
        <th>&nbsp;</th>
        <th align="center"><?php echo $nom_dir;?></th>
      </tr>
      <tr>
        <th height="22" align="center">PROFESOR JEFE</th>
        <th align="center">&nbsp;</th>
        <th align="center"><p>DIRECCI&Oacute;N</p></th>
      </tr>
    </table>
 </form>
</center>
</body>
</html>
