<?php


/** Attackers Soldier **/
function Set_Attackers($Soldier_Attack, $Soldier_Hitpoints, $Attackers)
{
    $Attackers_UnitAttack = ($Soldier_Attack * $Attackers) * (rand(95, 105) / 100);
    $Attackers_UnitHitpoints = $Soldier_Hitpoints * $Attackers;
    return array($Attackers_UnitAttack, $Attackers_UnitHitpoints);
}



/** Defenders Soldier **/
function Set_Defenders($Soldier_Attack, $Soldier_Hitpoints, $Defenders)
{
    $Defenders_UnitAttack = ($Soldier_Attack * $Defenders) * (rand(95, 105) / 100);
    $Defenders_UnitHitpoints = $Soldier_Hitpoints * $Defenders;
    return array($Defenders_UnitAttack, $Defenders_UnitHitpoints);
}