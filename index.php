<?php include 'includes/conn.php';?>
<?php include 'includes/functions.php';?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Combat Simulator</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <link href="css/custom.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/styles.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<form method="post">
    <div class="row">
        <div class="col-xs-12">
            <div class="col-xs-4 well text-center">
                <h2 style="color:blue;">Attackers</h2>
                <label>Soldiers </label>
                <input type="number" name="1_Soldier" placeholder="0" value="<?php echo $_POST['1_Soldier'] ?>" required="yes">
            </div>
            <div class="col-sm-4 well">
                <div class="col-xs-2 col-xs-offset-4">
                    <input class="btn btn-primary" type="submit" name="submit" value="Battle!"/><br><br>
                    <a class="btn btn-danger" href="index.php">Reset</a>
                </div>
            </div>
            <div class="col-xs-4 well text-center">
                <h2 style="color:red;">Defenders</h2>
                <label>Soldiers </label>
                <input type="number" name="2_Soldier" placeholder="0" value="<?php echo $_POST['2_Soldier'] ?>" required="yes">
            </div>
        </div>
    </div>
</form>

<div class="row">
<div class="col-xs-10 col-xs-offset-1 text-center">
    <?php

    if (isset($_POST['submit'])) {

        $round = 0;
        //Set army for both teams
        $Attackers = $_POST['1_Soldier'];
        $Defenders = $_POST['2_Soldier'];

        $sql = "SELECT * FROM Units";
        $result = mysqli_query($connection, $sql);
        $row = mysqli_fetch_array($result);

        echo "<table class='table'>";
        echo "<thead>";
        echo "<tr>" . "<th>Attackers</th>" . "<th>Deaths</th>" . "<th>Round</th>" . "<th>Deaths</th>" . "<th>Defenders</th>";
        echo "</thead>";
        echo "<tbody>";
        echo "<tr>";
        echo "<td>" . $Attackers . "</td>";
        echo "<td>0</td>";
        echo "<td>" . $round . "</td>";
        echo "<td>0</td>";
        echo "<td>" . $Defenders . "</td>";
        echo "</tr>";

        ////// Fetch Units Stats ///////////
        $sql = "SELECT * FROM Units";
        $result = mysqli_query($connection, $sql);
        $row = mysqli_fetch_array($result);

        ////// Fetch Units Stats ///////////
        $Soldier_Attack = $row['Attack'];
        $Soldier_Hitpoints = $row['Hitpoints'];

        $Sniper_Attack = $row['Attack'];
        $Sniper_Hitpoints = $row['Hitpoints'];
        ////// Fetch Units Stats ///////////




        // Check if both teams got units left
        while ($Attackers > 0 && $Defenders > 0) {

            // Set Attackers stats
            list($Attackers_UnitAttack, $Attackers_UnitHitpoints) = Set_Attackers($Soldier_Attack, $Soldier_Hitpoints, $Attackers);

            // Set Defenders stats
            list($Defenders_UnitAttack, $Defenders_UnitHitpoints) = Set_Defenders($Soldier_Attack, $Soldier_Hitpoints, $Defenders);

            // Remember original to calculate deaths
            $Attackers_Deaths = $Attackers;
            $Defenders_Deaths = $Defenders;

            // Battle
            $Attackers = ($Attackers_UnitHitpoints - $Defenders_UnitAttack) / $row['Hitpoints'];
            $Defenders = ($Defenders_UnitHitpoints - $Attackers_UnitAttack) / $row['Hitpoints'];

            // Prevents negative numbers and shows winner
            if ($Attackers < 0 ? $Attackers = 0 : $winner = "Blue team Wins!");
            if ($Defenders < 0 ? $Defenders = 0 : $winner = "Red team Wins!");

            // Calculate Deaths
            $Attackers_Deaths = $Attackers_Deaths - $Attackers;
            $Defenders_Deaths = $Defenders_Deaths - $Defenders;

            // Increase Round for each run
            $round++;


            echo "<tr>";
            echo "<td>" . floor($Attackers) . "</td>";
            echo "<td>" . floor($Attackers_Deaths) . "</td>";
            echo "<td>" . $round . "</td>";
            echo "<td>" . floor($Defenders_Deaths) . "</td>";
            echo "<td>" . floor($Defenders) . "</td>";
            echo "</tr>";


        }

        echo "<h1>" . $winner . "</h1>";

        echo "</tbody>";
        echo "</table>";



    }//end submit();


    ?>
</div>
</div>

</body>
</html>