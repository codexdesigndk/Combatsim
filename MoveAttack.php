<?php

class MoveAttack {

    public function __construct(Array $AttackingSide,Array $DefendingSide, $CalcTotals) {
        $this->Attackingside = $AttackingSide;
        $this->DefendingSide = $DefendingSide;
        $this->CalcTotals = $CalcTotals;
    }

    public function AttackorMove() {

        // Resets all units turns
        for ($Count = 0; $Count < count($this->Attackingside); $Count++) {
            $this->Attackingside[$Count]['TurnUsed'] = FALSE;
        }

        // Counts how many Attacking Unit types there is, and if the type have any units in them.
        for ($OuterCount = 0; $OuterCount < count($this->Attackingside); $OuterCount++) {
            if ($this->Attackingside[$OuterCount]['TotalUnits'] > 0) {

                // Counts how many Defending Unit Types there is, and if the type have any units in them.
                for ($InnerCount = 0; $InnerCount < count($this->DefendingSide); $InnerCount++) {
                    if ($this->DefendingSide[$InnerCount]['TotalUnits'] > 0  && $this->Attackingside[$OuterCount]['TurnUsed'] == FALSE) {

                        // Checks each attacking units against all defending units of their in range or not
                        // If there in range, it will attack, if not, then it will move closer and end its turn.
                        if (($this->Attackingside[$OuterCount]['Position'] + $this->DefendingSide[$InnerCount]['Position']) <= $this->Attackingside[$OuterCount]['Range']) {

                            // Attack and calculate kills
                            $this->DefendingSide[$InnerCount]['DeathsLastRound'] = floor(((($this->Attackingside[$OuterCount]['TotalUnits'] * $this->Attackingside[$OuterCount]['Attack']) * (rand(95, 105) / 100)) / $this->DefendingSide[$InnerCount]['Hitpoints']));

                            // Calculate remaining units
                            $this->DefendingSide[$InnerCount]['TotalUnits'] -= $this->DefendingSide[$InnerCount]['DeathsLastRound'];

                            // Check if unit is dead and set to 0.
                            if ($this->DefendingSide[$InnerCount]['TotalUnits'] < 0) {
                                $this->DefendingSide[$InnerCount]['DeathsLastRound'] += $this->DefendingSide[$InnerCount]['TotalUnits'];
                                $this->DefendingSide[$InnerCount]['TotalUnits'] = 0;
                            }

                            // Deduct deaths from total
                            $this->CalcTotals -= $this->DefendingSide[$InnerCount]['DeathsLastRound'];
                            $this->DefendingSide[$InnerCount]['TotalDeaths'] += $this->DefendingSide[$InnerCount]['DeathsLastRound'];

                            $this->Attackingside[$OuterCount]['TurnUsed'] = TRUE;

                        } else { // Move Phrase

                            // Move Unit´s Range by deducting it from its current position.
                            $this->Attackingside[$OuterCount]['Position'] -= $this->Attackingside[$OuterCount]['Speed'];

                            $this->Attackingside[$OuterCount]['TurnUsed'] = TRUE;
                        }



                    }
                }
            }
        }
        return array( $this->Attackingside, $this->DefendingSide, $this->CalcTotals);
    }
}

