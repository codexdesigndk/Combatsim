<?php include 'includes/conn.php'; ?>
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
    <!-- Custom styles for this template -->
    <link href="css/custom.css" rel="stylesheet">

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
                <input type="number" name="A_Unit_1" placeholder="0" value="<?php echo $_POST['A_Unit_1'] ?>"
                       required="yes"><br>
                <label>Snipers </label>
                <input type="number" name="A_Unit_2" placeholder="0" value="<?php echo $_POST['A_Unit_2'] ?>"
                       required="yes">
            </div>
            <div class="col-sm-4 well">
                <div class="col-xs-2 col-xs-offset-5">
                    <input class="btn btn-primary" type="submit" name="submit" value="Battle!"/><br><br>
                    <a class="btn btn-danger" href="index.php">Reset</a>
                </div>
            </div>
            <div class="col-xs-4 well text-center">
                <h2 style="color:red;">Defenders</h2>
                <label>Soldiers </label>
                <input type="number" name="D_Unit_1" placeholder="0" value="<?php echo $_POST['D_Unit_1'] ?>"
                       required="yes"><br>
                <label>Snipers </label>
                <input type="number" name="D_Unit_2" placeholder="0" value="<?php echo $_POST['D_Unit_2'] ?>"
                       required="yes">
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
            $AttackingUnits = array($_POST['A_Unit_1'], $_POST['A_Unit_2']);
            $DefendingUnits = array($_POST['D_Unit_1'], $_POST['D_Unit_2']);

            echo "<table class='table'>";
            echo "<thead>";
            echo "<tr>" . "<th>Attackers</th>" . "<th>Deaths</th>" . "<th>Round</th>" . "<th>Deaths</th>" . "<th>Defenders</th>";
            echo "</thead>";
            echo "<tbody>";
            echo "<tr>";
            echo "<td></td>";
            echo "<td>0</td>";
            echo "<td>" . $round . "</td>";
            echo "<td>0</td>";
            echo "<td></td>";
            echo "</tr>";

            ////// Fetch Units Stats ///////////
            $sql = "SELECT * FROM Units";
            $result = mysqli_query($connection, $sql);


            // Set Army "Total Units" / "Total Damage" / "Total Hitpoints" for Attackers and Defenders
            $AttackersTotal = 0;
            $DefendersTotal = 0;

            /**
             * Calculate Army statistics such as total units, combined damage and hitpoints.
             */

            $rowcount = -1;
            while ($row = mysqli_fetch_array($result)) {
                $rowcount++;
                $Attackers[] = $row;
                $Attackers[$rowcount]['Total Units'] = $AttackingUnits[$rowcount];
                $Attackers[$rowcount]['Combined Damage'] = ($AttackingUnits[$rowcount] * $Attackers[$rowcount]['Attack']) * (rand(95, 105) / 100);
                $Attackers[$rowcount]['Combined Hitpoints'] = ($AttackingUnits[$rowcount] * $Attackers[$rowcount]['Hitpoints']);
                $Attackers[$rowcount]['Position'] = 0;
                $AttackersTotal = $AttackersTotal + $AttackingUnits[$rowcount];


                $Defenders[] = $row;
                $Defenders[$rowcount]['Total Units'] = $DefendingUnits[$rowcount];
                $Defenders[$rowcount]['Combined Damage'] = ($DefendingUnits[$rowcount] * $Defenders[$rowcount]['Attack']) * (rand(95, 105) / 100);
                $Defenders[$rowcount]['Combined Hitpoints'] = ($DefendingUnits[$rowcount] * $Defenders[$rowcount]['Hitpoints']);
                $Defenders[$rowcount]['Position'] = 0;
                $DefendersTotal = $DefendersTotal + $DefendingUnits[$rowcount];

            }

            /**
             * Find Max range of each army
             */

            // Find MAX Range on all units for Attackers and Defenders
            $AttackersMax = ~PHP_INT_MAX;;
            foreach ($Attackers as $key => $value) {
                if ($value['Range'] > $AttackersMax) {
                    $AttackersMax = $value['Range'];
                }
            }
            $DefendersMax = ~PHP_INT_MAX;;
            foreach ($Defenders as $key => $value) {
                if ($value['Range'] > $DefendersMax) {
                    $DefendersMax = $value['Range'];
                }
            }


            /**
             * Setting each army at max positions
             */

            // Set Attackers units at Max Range
            for ($rowcount = 0; $rowcount < count($Attackers); $rowcount++) {
                $Attackers[$rowcount]['Position'] = $AttackersMax;
            }
            // Set Defenders units at Max Range
            for ($rowcount = 0; $rowcount < count($Defenders); $rowcount++) {
                $Defenders[$rowcount]['Position'] = $DefendersMax;
            }

            /*
            echo "<pre>";
            print_r($Attackers);
            echo "</pre>";
            echo "<pre>";
            print_r($Defenders);
            echo "</pre>";
            */

            /**
             * This is the Move and Attack Phrase.
             */

            // Check if both teams got units left.
            while ($AttackersTotal > 0 && $DefendersTotal > 0) {

                // Counts how many Attacking Unit types there is, and if the type have any units in them.
                for ($rowcount = 0; $rowcount < count($Attackers); $rowcount++) {
                    if ($Attackers[$rowcount]['Total Units'] > 0) {

                        // Counts how many Defending Unit Types there is, and if the type have any units in them.
                        for ($defrowcount = 0; $defrowcount < count($Defenders); $defrowcount++) {
                            if ($Defenders[$defrowcount]['Total Units'] > 0) {

                                // Checks each attacking units against all defending units of their in range or not
                                // If there in range, it will attack, if not, then it will move closer and end its turn.
                                if (($Attackers[$rowcount]['Position'] + $Defenders[$defrowcount]['Position']) <= $Attackers[$rowcount]['Range']) {

                                    /**
                                     * This is test code here below for recalculating damage. (Work on this now)
                                     */
                                    $Attackers[$rowcount]['Combined Damage'] = ($AttackingUnits[$rowcount] * $Attackers[$rowcount]['Attack']) * (rand(95, 105) / 100);

                                    // echo "Attackers: " . $Attackers[$rowcount]['Name'] . "<br>";
                                    // echo "Attack<br>";

                                    // echo "Units Before Fight: " . $Defenders[$defrowcount]['Total Units'] . "<br>";

                                    // Give Attacking Unit's Damage to Defending Unit's Hitpoints.
                                    $Defenders[$defrowcount]['Combined Hitpoints'] -= $Attackers[$rowcount]['Combined Damage'];

                                    // Calculate Defenders Deaths (NEED Better way here to calculate deaths)
                                    $Defenders[$defrowcount]['DeathsLastRound'] = floor(($Attackers[$rowcount]['Combined Damage'] / $Defenders[$defrowcount]['Hitpoints']));

                                    // echo "Deaths: " . $Defenders[$defrowcount]['DeathsLastRound'] . "<br>";

                                    // Calculate remaining units
                                    $Defenders[$defrowcount]['RemainingUnits'] = ceil(($Defenders[$defrowcount]['Combined Hitpoints'] / $Defenders[$defrowcount]['Hitpoints']));

                                    // echo "Remaining Units: " . $Defenders[$defrowcount]['RemainingUnits'] . "<br>";

                                    // Deduct deaths from Unit Type
                                    $Defenders[$defrowcount]['Total Units'] -= $Defenders[$defrowcount]['DeathsLastRound'];
                                    // echo "<br>";

                                    // Deduct deaths from total
                                    $DefendersTotal -= $Defenders[$defrowcount]['DeathsLastRound'];

                                } else { // Move Phrase
                                    // echo "Attackers: " . $Attackers[$rowcount]['Name'] . "<br>";
                                    // echo "Move<br>";

                                    // Move Unit´s Range by deducting it from its current position.
                                    $Attackers[$rowcount]['Position'] -= $Attackers[$rowcount]['Speed'];
                                    // echo "<br>";
                                }
                            }
                        }
                    }
                }

// Counts how many Defending Unit Types there is, and if the type have any units in them.


                for ($defrowcount = 0; $defrowcount < count($Defenders); $defrowcount++) {
                    if ($Defenders[$defrowcount]['Total Units'] > 0) {

                        // Counts how many Attacking Unit types there is, and if the type have any units in them.
                        for ($atkrowcount = 0; $atkrowcount < count($Attackers); $atkrowcount++) {
                            if ($Attackers[$atkrowcount]['Total Units'] > 0) {

                                // Checks each attacking units against all defending units of their in range or not
                                // If there in range, it will attack, if not, then it will move closer and end its turn.
                                if (($Defenders[$defrowcount]['Position'] + $Attackers[$atkrowcount]['Position']) <= $Defenders[$defrowcount]['Range']) {

                                    // echo "Defenders: " . $Defenders[$defrowcount]['Name'] . "<br>";
                                    // echo "Attack<br>";

                                    // echo "Units Before Fight: " . $Attackers[$atkrowcount]['Total Units'] . "<br>";

                                    // Give Attacking Unit's Damage to Defending Unit's Hitpoints.
                                    $Attackers[$atkrowcount]['Combined Hitpoints'] -= $Defenders[$defrowcount]['Combined Damage'];
                                    // echo "Hitpoints of attackers after: ".$Attackers[$atkrowcount]['Combined Hitpoints'] . "<br>";

                                    // Calculate Attackers Deaths
                                    $Attackers[$atkrowcount]['DeathsLastRound'] = floor(($Defenders[$defrowcount]['Combined Damage'] / $Attackers[$atkrowcount]['Hitpoints']));
                                    echo "Deaths: " . $Attackers[$atkrowcount]['DeathsLastRound'] . "<br>";

                                    // Calculate remaining units
                                    $Attackers[$atkrowcount]['RemainingUnits'] = ceil(($Attackers[$atkrowcount]['Combined Hitpoints'] / $Attackers[$atkrowcount]['Hitpoints']));
                                    // echo "Remaining Units: " . $Attackers[$atkrowcount]['RemainingUnits'] . "<br>";

                                    // Deduct deaths from Unit Type
                                    $Attackers[$atkrowcount]['Total Units'] -= $Attackers[$atkrowcount]['DeathsLastRound'];
                                    // echo "<br>";

                                    // Deduct deaths from total
                                    $AttackersTotal -= $Attackers[$atkrowcount]['DeathsLastRound'];

                                } else {

                                    // echo "Defenders: " . $Defenders[$defrowcount]['Name'] . "<br>";
                                    // echo "Move<br>";

                                    // Move Unit´s Range by deducting it from its current position.
                                    $Defenders[$defrowcount]['Position'] -= $Defenders[$defrowcount]['Speed'];
                                    // echo "<br>";
                                }
                            }
                        }
                    }
                }


                // Increase Round for each run
                $round++;


                echo "<tr>";
                echo "<td>";
                echo $Attackers[0]['Name'] . ": " . $Attackers[0]['Total Units'] . "<br>";
                echo $Attackers[1]['Name'] . ": " . $Attackers[1]['Total Units'] . "<br>";
                echo "</td>";
                echo "<td>";
                echo floor(isset($Attackers[0]['DeathsLastRound']) ? $Attackers[0]['DeathsLastRound'] : 0) . "<br>";
                echo floor(isset($Attackers[1]['DeathsLastRound']) ? $Attackers[1]['DeathsLastRound'] : 0);
                echo "</td>";
                echo "<td>" . $round . "</td>";
                echo "<td>";
                echo floor(isset($Defenders[0]['DeathsLastRound']) ? $Defenders[0]['DeathsLastRound'] : 0) . "<br>";
                echo floor(isset($Defenders[1]['DeathsLastRound']) ? $Defenders[1]['DeathsLastRound'] : 0);
                echo "</td>";                echo "<td>";
                echo $Defenders[0]['Name'] . ": " . $Defenders[0]['Total Units'] . "<br>";
                echo $Defenders[1]['Name'] . ": " . $Defenders[1]['Total Units'] . "<br>";
                echo "</td>";
                echo "</tr>";


            }



            echo "<h1></h1>";

            echo "</tbody>";
            echo "</table>";


        }//end submit();


        ?>
    </div>
</div>

</body>
</html>