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

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="includes/DataTables-1.10.4/media/css/jquery.dataTables.css">

    <!-- jQuery -->
    <script type="text/javascript" charset="utf8" src="includes/DataTables-1.10.4/media/js/jquery.js"></script>

    <!-- DataTables -->
    <script type="text/javascript" charset="utf8" src="includes/DataTables-1.10.4/media/js/jquery.dataTables.js"></script>

</head>
<body>


<div class="row">
    <div class="col-xs-12 well">
        <form method="post">
            <div class="col-xs-4">
                <h2 style="color:red;">Attackers</h2>
                <div class="col-xs-6">
                    <label>Soldiers </label><br>
                    <label>Snipers </label><br>
                    <label>Grenadiers </label>
                </div>
                <div class="col-xs-6">
                    <input type="number" name="A_Unit_1" placeholder="0" value="<?php echo $_POST['A_Unit_1'] ?>" required="yes"><br>
                    <input type="number" name="A_Unit_2" placeholder="0" value="<?php echo $_POST['A_Unit_2'] ?>" required="yes"><br>
                    <input type="number" name="A_Unit_3" placeholder="0" value="<?php echo $_POST['A_Unit_3'] ?>" required="yes">
                </div>
            </div>
            <div class="col-xs-4 text-center">
                <input class="btn btn-primary" type="submit" name="submit" value="Battle!"/><br><br>
                <a class="btn btn-danger" href="index.php">Reset</a>
            </div>
            <div class="col-xs-4">
                <h2 style="color:blue;">Defenders</h2>
                <div class="col-xs-6">
                    <label>Soldiers </label><br>
                    <label>Snipers </label><br>
                    <label>Grenadiers </label>
                </div>
                <div class="col-xs-6">
                    <input type="number" name="D_Unit_1" placeholder="0" value="<?php echo $_POST['D_Unit_1'] ?>" required="yes"><br>
                    <input type="number" name="D_Unit_2" placeholder="0" value="<?php echo $_POST['D_Unit_2'] ?>" required="yes"><br>
                    <input type="number" name="D_Unit_3" placeholder="0" value="<?php echo $_POST['D_Unit_3'] ?>" required="yes">
                </div>
            </div>
        </form>
    </div>
</div> <!-- End Row -->


<div class="row">
    <div class="col-xs-8 col-xs-offset-2 text-center">
        <?php

        if (isset($_POST['submit'])) {
            $round = 0;

            //Set army for both teams
            $AttackingUnits = array($_POST['A_Unit_1'], $_POST['A_Unit_2'], $_POST['A_Unit_3']);
            $DefendingUnits = array($_POST['D_Unit_1'], $_POST['D_Unit_2'], $_POST['D_Unit_3']);

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
                $Attackers[$rowcount]['TotalUnits'] = $AttackingUnits[$rowcount];
                $AttackersTotal = $AttackersTotal + $AttackingUnits[$rowcount];

                $Defenders[] = $row;
                $Defenders[$rowcount]['TotalUnits'] = $DefendingUnits[$rowcount];
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

            require_once 'MoveAttack.php';

            echo "<table id='Rounds' class='table table-striped table-hover'>";
            echo "<thead>";
            echo "<tr>" . "<th>Troop</th>" . "<th>Casualties</th>" . "<th>Round</th>" . "<th>Troop</th>" . "<th>Casualties</th>" . "</tr>";
            echo "</thead>";
            echo "<tbody>";


            /**
             * This is the Move and Attack Phrase.
             *
             */
            // Check if both teams got units left.
            while ($AttackersTotal > 0 && $DefendersTotal > 0) {

                // Attackers vs Defenders Turn
                $Battle = new MoveAttack($Attackers, $Defenders, $DefendersTotal);
                list ($Attackers, $Defenders, $DefendersTotal) = $Battle->AttackorMove();

                // Defenders vs Attackers Turn
                $Battle = new MoveAttack($Defenders, $Attackers, $AttackersTotal);
                list ($Defenders, $Attackers, $AttackersTotal) = $Battle->AttackorMove();

                /**
                 * Setting variables for the Results Window
                 */
/*
                echo "<pre>";
                echo print_r($Attackers);
                echo "</pre>";
*/
                // Increase Round for each run
                $round++;

                echo "<tr>";
                echo "<td>";
                for ($Count = 0; $Count < count($Attackers); $Count++) {
                    echo $Attackers[$Count]['Name'] . ": " . $Attackers[$Count]['TotalUnits'] . "(" . $Attackers[$Count]['Position'] . ")" . "<br>";
                }
                echo "</td>";
                echo "<td>";
                for ($Count = 0; $Count < count($Attackers); $Count++) {
                    echo floor(isset($Attackers[$Count]['DeathsLastRound']) ? $Attackers[$Count]['DeathsLastRound'] : 0) . "<br>";
                    if ($Attackers[$Count]['TotalUnits'] == 0 && (isset($Attackers[$Count]['DeathsLastRound']))) {
                        $Attackers[$Count]['DeathsLastRound'] = 0;
                    }
                }
                echo "</td>";
                echo "<td>" . $round . "</td>";
                echo "<td>";
                for ($Count = 0; $Count < count($Defenders); $Count++) {
                    echo $Defenders[$Count]['Name'] . ": " . $Defenders[$Count]['TotalUnits'] . "(" . $Defenders[$Count]['Position'] . ")" . "<br>";
                }
                echo "</td>";
                echo "<td>";
                for ($Count = 0; $Count < count($Attackers); $Count++) {
                    echo floor(isset($Defenders[$Count]['DeathsLastRound']) ? $Defenders[$Count]['DeathsLastRound'] : 0) . "<br>";
                    if ($Defenders[$Count]['TotalUnits'] == 0 && (isset($Defenders[$Count]['DeathsLastRound']))) {
                        $Defenders[$Count]['DeathsLastRound'] = 0;
                    }
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        }//end submit();

        ?>
    </div> <!-- Div with While class -->
    <script>
        $(function(){
            $('#Rounds').dataTable( {
                "order": [[ 2, "asc" ]],
                "aLengthMenu": [[0, 5, 10, 25, -1], [0, 5, 10, 25, "All"]],
                "iDisplayLength": 0
            } );
        })
    </script>
</div> <!-- End Row -->


<div class="row">
    <div class="col-xs-8 col-md-offset-2 ReportWindow">
            <div class="col-xs-6 text-center">
                <h2>Attackers</h2>
                <div class="col-xs-12 innerresults">
                        <?php
                        echo "<table class='table'>";
                        echo "<thead>";
                        echo "<tr>" . "<th>Troop</th>" . "<th>Amount</th>" . "<th>Casualty</th>" . "</tr>";
                        echo "</thead>";
                        echo "<tbody>";
                        if (isset($Attackers)) {
                            for ($Count = 0; $Count < count($Attackers); $Count++) {
                                if ($Attackers[$Count]['TotalUnits'] + $Attackers[$Count]['TotalDeaths'] != 0) {
                                    echo "<tr>";
                                    echo "<td>";
                                    echo $Attackers[$Count]['Name'];
                                    echo "</td>";
                                    echo "<td>";
                                    echo $Attackers[$Count]['TotalUnits'] += $Attackers[$Count]['TotalDeaths'];
                                    echo "</td>";
                                    echo "<td>";
                                    echo $Attackers[$Count]['TotalDeaths'];
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            }
                        }
                        echo "</tbody>";
                        echo "</table>";
                        ?>
                </div>
            </div>

            <div class="col-xs-6 text-center">
                <h2>Defenders</h2>
                <div class="col-xs-12 innerresults">
                        <?php
                        echo "<table class='table'>";
                        echo "<thead>";
                        echo "<tr>" . "<th>Troop</th>" . "<th>Amount</th>" . "<th>Casualty</th>" . "</tr>";
                        echo "</thead>";
                        echo "<tbody>";
                        if (isset($Defenders)) {
                            for ($Count = 0; $Count < count($Defenders); $Count++) {
                                if ($Defenders[$Count]['TotalUnits'] + $Defenders[$Count]['TotalDeaths'] != 0) {
                                    echo "<tr>";
                                    echo "<td>";
                                    echo $Defenders[$Count]['Name'];
                                    echo "</td>";
                                    echo "<td>";
                                    echo $Defenders[$Count]['TotalUnits'] += $Defenders[$Count]['TotalDeaths'];
                                    echo "</td>";
                                    echo "<td>";
                                    echo $Defenders[$Count]['TotalDeaths'];
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            }
                        }
                        echo "</tbody>";
                        echo "</table>";
                        ?>
                </div>
            </div>
    </div>
</div>
<!-- End Row -->


</body>
</html>