<?php
     function calculer_date($date,$difference,$format=NULL){
     if (!$format) $format='Y-m-d H:i:s';
     $date=date($format, strtotime ($date."$difference"));
     
     return $date;
     
}     

?>