<?php

include_once("hero.php");

class PitController
{
  function oneOnOne($hero1, $hero2)
  {
    if ($hero1->CurrentHP <= 0) {
		echo "Hero 1 has no HP!<br />";
		return;
	}
    if ($hero2->CurrentHP <= 0) {
		echo "Hero 2 has no HP!<br />";
		return;
	}
	echo $hero1->Name . " Level " . $hero1->Level . " is fighting " . $hero2->Name . " Level " . $hero2->Level . "<br />";
	
	$fighters = array(array($hero1, 0), array($hero2, 0));
	$aggressor = $this->chooseFirst($hero1, $hero2);
	echo $fighters[$aggressor][0]->Name . " is going 1st<br />";
	
	$fighting = true;
	$winner = null;
	while ($fighting) {
	  $target = ($aggressor + 1) % 2;
	  
	  $damageDelt = $fighters[$target][0]->takeDamage($fighters[$aggressor][0]->calcDamage());
	  echo $fighters[$aggressor][0]->Name . " did " . $damageDelt . "<br />";
	  
	  //increase runaway possibility
	  if($damage > $fighters[$target][0]->calculateAttributeBonus($fighters[$target][0]->Con))
	  {
		if($fighters[$target][0]->Cha < $fighters[aggressor][0]->Cha)
		{
			$fighter[$target][1] += 2;
			echo $fighters[$target][0]->Name . "'s run away increased to " . $fighters[$target][1] . "<br />";
		}
		else
		{
			$fighter[$target][1] += 1;
			echo $fighters[$target][0]->Name . "'s run away increased to " . $fighters[$target][1] . "<br />";
		}
	  }
	  
	  if ($fighters[$target][0]->CurrentHP <= 0) {
	    $winner = $fighters[$aggressor][0];
		$fighting = false;
		if ($fighters[$target][0]->CurrentHP <= -$fighters[$target][0]->Con) {
		  echo $fighters[$target][0]->Name . " died <br />";
		} else {
		  echo $fighters[$target][0]->Name . " was knocked out <br />";
		}
		break;
	  }
	  
	  if($fighters[$target][1] > $fighter[$target][0]->Level)
	  {
		$winner = $fighters[$aggressor][0];
		$fighting = false;
		echo $fighters[$target][0]->Name . " decided to run away <br />";
		break;
	  }
	  
	  $aggressor = $target;
    }
  }
  
  function chooseFirst($hero1, $hero2)
  {
    $init1 = $hero1->rollInitiative();
    $init2 = $hero2->rollInitiative();
	
	if ($init1 > $init2) {
	  return 0;
	}
	else if ($init2 > $init1) {
	  return 1;
	}
	else if ($hero1->Fte > $hero2->Fte) {
	  return 0;
	}
	else if ($hero2->Fte > $hero1->Fte) {
	  return 1;
	}
	else {
	  return rand(0,1);
	}
  }
}