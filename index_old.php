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
             * Calculate Army statistics.
             */
            $rowcount = -1;
            while ($row = mysqli_fetch_array($result)) {
                $rowcount++;
                $Attackers[] = $row;
                $Attackers[$rowcount]['Total Units'] = $AttackingUnits[$rowcount];
                $AttackersTotal = $AttackersTotal + $AttackingUnits[$rowcount];

                $Defenders[] = $row;
                $Defenders[$rowcount]['Total Units'] = $DefendingUnits[$rowcount];
                $DefendersTotal = $DefendersTotal + $DefendingUnits[$rowcount];
            }

            /**
             * Find Max range of each army, tos
             */
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
            for ($rowcount = 0; $rowcount < count($Attackers); $rowcount++) {
                $Attackers[$rowcount]['Position'] = $AttackersMax;
            }
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
             *
             * Attackers code is located first, which results in them being hit first by the defenders
             * aswell. This is to give advantages to defenders.
             */

            // Check if both teams got units left.
            while ($AttackersTotal > 0 && $DefendersTotal > 0) {


                // Counts how many Attacking Unit types there is, and if the type have any units in them.
                for ($OuterCount = 0; $OuterCount < count($Attackers); $OuterCount++) {
                    if ($Attackers[$OuterCount]['Total Units'] > 0) {

                        // Counts how many Defending Unit Types there is, and if the type have any units in them.
                        for ($InnerCount = 0; $InnerCount < count($Defenders); $InnerCount++) {
                            if ($Defenders[$InnerCount]['Total Units'] > 0) {

                                // Checks each attacking units against all defending units of their in range or not
                                // If there in range, it will attack, if not, then it will move closer and end its turn.
                                if (($Attackers[$OuterCount]['Position'] + $Defenders[$InnerCount]['Position']) <= $Attackers[$OuterCount]['Range']) {

                                    // Attack and calculate kills
                                    $Defenders[$InnerCount]['DeathsLastRound'] = floor((($Attackers[$OuterCount]['Total Units'] * $Attackers[$OuterCount]['Attack']) / $Defenders[$InnerCount]['Hitpoints']));

                                    // Calculate remaining units
                                    $Defenders[$InnerCount]['Total Units'] -= $Defenders[$InnerCount]['DeathsLastRound'];

                                    // Check if unit is dead and set to 0.
                                    if ($Defenders[$InnerCount]['Total Units'] < 0) {
                                        $Defenders[$InnerCount]['DeathsLastRound'] += $Defenders[$InnerCount]['Total Units'];
                                        $Defenders[$InnerCount]['Total Units'] = 0;
                                    }

                                    // Deduct deaths from total
                                    $DefendersTotal -= $Defenders[$InnerCount]['DeathsLastRound'];

                                } else { // Move Phrase

                                    // Move Unit´s Range by deducting it from its current position.
                                    $Attackers[$OuterCount]['Position'] -= $Attackers[$OuterCount]['Speed'];

                                }
                            }
                        }
                    }
                }

// Counts how many Defending Unit Types there is, and if the type have any units in them.

                for ($OuterCount = 0; $OuterCount < count($Defenders); $OuterCount++) {
                    if ($Defenders[$OuterCount]['Total Units'] > 0) {

                        // Counts how many Attacking Unit types there is, and if the type have any units in them.
                        for ($InnerCount = 0; $InnerCount < count($Attackers); $InnerCount++) {
                            if ($Attackers[$InnerCount]['Total Units'] > 0) {

                                // Checks each attacking units against all defending units of their in range or not
                                // If there in range, it will attack, if not, then it will move closer and end its turn.
                                if (($Defenders[$OuterCount]['Position'] + $Attackers[$InnerCount]['Position']) <= $Defenders[$OuterCount]['Range']) {

                                    // Attack and calculate kills
                                    $Attackers[$InnerCount]['DeathsLastRound'] = floor((($Defenders[$OuterCount]['Total Units'] * $Defenders[$OuterCount]['Attack']) / $Attackers[$InnerCount]['Hitpoints']));

                                    // Calculate remaining units
                                    $Attackers[$InnerCount]['Total Units'] -= $Attackers[$InnerCount]['DeathsLastRound'];

                                    // Check if unit is dead and set to 0.
                                    if ($Attackers[$InnerCount]['Total Units'] < 0) {
                                        $Attackers[$InnerCount]['DeathsLastRound'] += $Attackers[$InnerCount]['Total Units'];
                                        $Attackers[$InnerCount]['Total Units'] = 0;
                                    }

                                    // Deduct deaths from total
                                    $AttackersTotal -= $Attackers[$InnerCount]['DeathsLastRound'];

                                } else {

                                    // Move Unit´s Range by deducting it from its current position.
                                    $Defenders[$OuterCount]['Position'] -= $Defenders[$OuterCount]['Speed'];

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


            echo "</tbody>";
            echo "</table>";

        }//end submit();


        ?>
    </div>
</div>

</body>
</html>