<?php

function build_calendar($month, $year) {
    $mysqli = new mysqli('localhost', 'root', '', 'bookingcalendar');
    $stmt = $mysqli->prepare("select * from tb_bookings where MONTH(date) = ? AND YEAR(date) = ?");
    $stmt->bind_param('ss', $month, $year);
    $tb_bookings = array();
    if($stmt->execute()) {
      $result = $stmt->get_result();
      if($result->num_rows>0){
        while($row = $result->fetch_assoc()) {
          $tb_bookings[] = $row['date'];
        }
        $stmt-> close();
      }
    }



    // Create array containing abbreviations of days of week.
    $daysOfWeek = array('Segunda','Terça','Quarta','Quinta','Sexta','Sábado', 'Domingo' );

    // What is the first day of the month in question?
    $firstDayOfMonth = mktime(0,0,0,$month,1,$year);

    // How many days does this month contain?
    $numberDays = date('t',$firstDayOfMonth);

    // Retrieve some information about the first day of the
    // month in question.
    $dateComponents = getdate($firstDayOfMonth);

    // What is the name of the month in question?
    $monthName = $dateComponents['month'];

    // What is the index value (0-6) of the first day of the
    // month in question.
    $dayOfWeek = $dateComponents['wday'];
    if($dayOfWeek==0) {
      $dayOfWeek=6;
    } else {
      $dayOfWeek = $dayOfWeek-1;
    }

    // Create the table tag opener and day headers
    
    $dateToday = date('d-m-y');

    $calendar = "<table class='table table-bordered'>";
    $calendar .= "<h2>$monthName $year</h2>";
    $calendar.= "<a class='btn btn-xs btn-pink' href='?month=".date('m', mktime(0, 0, 0, $month-1, 1, $year))."&year=".date('Y', mktime(0, 0, 0, $month-1, 1, $year))."'><</a> ";
    
    $calendar.= "<a class='btn btn-xs btn-pink' href='?month=".date('m')."&year=".date('Y')."'>Mês atual</a> ";

    $calendar.= "<a class='btn btn-xs btn-pink' href='?month=".date('m', mktime(0, 0, 0, $month+1, 1, $year))."&year=".date('Y', mktime(0, 0, 0, $month+1, 1, $year))."'>></a><br>";
    
    
    $calendar .= "<tr>";

    //Creating the calendar headers
    foreach($daysOfWeek as $day) {
      $calendar .= "<th  class='header'>$day</th>";
    } 

    $currentDay = 1;
    $calendar .= "<tr></tr>";

    //The variable $dayOfWeek will make sure that the must be only 7 columns on our table
    if ($dayOfWeek > 0) { 
      for($k=0;$k<$dayOfWeek;$k++){
            $calendar .= "<td  class='empty'></td>";
      }
    }

    //initiating the day counter
    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
    
    while ($currentDay <= $numberDays){

    // Seventh column (Saturday) reached. Start a new row.
    if ($dayOfWeek == 7) {

      $dayOfWeek = 0;
      $calendar .= "</tr><tr>";
    }

      $currentDayRel = str_pad($currentDay,2,"0",STR_PAD_LEFT);
      $date = "$year-$month-$currentDayRel";

      $dayname = strtolower(date("l", strtotime($date)));
      $eventNum = 0;
      $today = $date==date('d-m-Y')?"today":"";

    if($dayname=='sunday'){
      $calendar.="<td><h4>$currentDay</h4> <button class='btn btn-danger btn-xs'>Fechado</button>";
    } elseif($date<date('Y-m-d')){
      $calendar.="<td><h4>$currentDay</h4> ";
    }else{

      $totalbookings = checkSlots($mysqli, $date);
    if($totalbookings==9) {
      $calendar.="<td class='$today'><h4>$currentDay</h4> <a href='book.php?date=".$date."' class='btn btn-danger btn-xs'>Vagas esgotadas</a>";      
    } else {
      $availableslots = 9 - $totalbookings;
      $calendar.="<td class='$today'><h4>$currentDay</h4> <a href='book.php?date=".$date."' class='btn btn-success btn-xs'>Disponível</a>
      <small><i>$availableslots vagas</i></small>  ";   
    }
    }


    $calendar .= "</td>";
   

    //incrementing the counters
    $currentDay++;
    $dayOfWeek++;
    }

    //completing the row of the last week in month, if necessary
    if ($dayOfWeek != 7) { 
      
      $remainingDays = 7 - $dayOfWeek;
        for($l=0;$l<$remainingDays;$l++){
            $calendar .= "<td class='empty'></td>"; 
      }
    }

    $calendar .="</tr>";
    $calendar .="</table>";

    echo $calendar;

    }

  function checkSlots($mysqli, $date) {
    $stmt = $mysqli->prepare("select * from tb_bookings where date = ?");
    $stmt->bind_param('s', $date);
    $totalbookings = 0;
    if($stmt->execute()) {
      $result = $stmt->get_result();
      if($result->num_rows>0){
        while($row = $result->fetch_assoc()) {
          $totalbookings++;
        }
        $stmt-> close();
      }
    }
    return $totalbookings;
  }
  ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/global.css">
    <link rel="stylesheet" href="../style/sidebar.css">
    <link rel="stylesheet" href="../style/main.css">
    <link rel="stylesheet" href="../style/perfil.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
  
    <title>Nails - Perfil</title>
</head>
<body>
  <div id="flex-dashboard">
  <?php include('sidebar.php'); ?>
    
        <main>
            <header>
                <div class="menu-bar">
                    <a href="">
                        <img src="https://image.flaticon.com/icons/svg/1828/1828859.svg" alt="">
                    </a>
                </div>
                <div class="menu-profile">
                    <a href="perfil.php">
                        <img src="https://image.flaticon.com/icons/svg/2948/2948035.svg" alt="">
                    </a>
                </div>
                
            </header>                
 
            <div class="main-content">
              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <?php
                      $dateComponents = getdate();
                      if(isset($_GET['month']) && isset($_GET['year'])){
                          $month = $_GET['month']; 			     
                          $year = $_GET['year'];
                      }else{
                          $month = $dateComponents['mon']; 			     
                          $year = $dateComponents['year'];
                      }
                      echo build_calendar($month,$year);
                  ?>
                </div>
              </div>
            </div>              
          </div>
        </main>
    </div>  

    <script src="../script/fiel-birth.js"></script>
    <script src="../script/perfil-services.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>
</html>