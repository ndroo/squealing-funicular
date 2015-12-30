<?php

include_once("race.php");
include_once("heroClass.php");
include_once("weapon.php");

class Hero
/*
//////perhaps have 2 weapons and an armor or 3 items of some sort to make things more indepth, laters problem.
/////// Death from old age? max age based on race? 1day IRL = 1 year
*/
{
  public $ID;
  public $OwnerID;
  public $PartyID;
  public $Name;
  public $Race;
  public $HeroClass;
  public $MaxHP;
  public $CurrentHP;
  public $Level;
  public $CurrentXP;
  public $LevelUpXP;
  public $Str;
  public $Dex;
  public $Con;
  public $Intel;
  public $Wis;
  public $Cha;
  public $Fte;
  public $WeaponID;
  
  function __construct()
  {

  }
  
  //load Adventurer from DB 
  function loadHero($ID)
  {
    $this->ID = $ID;
    //check ID is not blank and exists and such
    $getQuery = "SELECT * FROM `Hero` WHERE `ID` = '" . $this->ID . "';";

    $getResult=mysql_query($getQuery);//execute query
    $num=mysql_numrows($getResult);
    
    $this->OwnerID =  mysql_result($getResult,0,"OwnerID");
    $this->PartyID =  mysql_result($getResult,0,"PartyID");
    $this->Race = Race::loadRace(mysql_result($getResult,0,"Race"));
    $this->Name = mysql_result($getResult,0,"Name");
    $this->HeroClass = HeroClass::loadHeroClass(mysql_result($getResult,0,"Class"));
    //$this->HeroClass = mysql_result($getResult,0,"Class");//load object
    $this->MaxHP = mysql_result($getResult,0,"MaxHP");
    $this->CurrentHP = mysql_result($getResult,0,"CurrentHP");
    $this->Level = mysql_result($getResult,0,"Level");
    $this->CurrentXP = mysql_result($getResult,0,"CurrentXP");
    $this->LevelUpXP = mysql_result($getResult,0,"LevelUpXP");
    $this->Str = mysql_result($getResult,0,"Str");
    $this->Dex = mysql_result($getResult,0,"Dex");
    $this->Con = mysql_result($getResult,0,"Con");
    $this->Intel = mysql_result($getResult,0,"Intel");
    $this->Wis = mysql_result($getResult,0,"Wis");
    $this->Cha = mysql_result($getResult,0,"Cha");
    $this->Fte = mysql_result($getResult,0,"Fte");
    $this->WeaponID = mysql_result($getResult,0,"WeaponID");
    
    return $this;
  }
  
  function GenerateHero($level)// change to generate all characters at level 1 then call level up from a loop to add levels
  {
    //Race
    $this->Race = $this->GenerateRace();
    echo "Race: " . $this->Race->Name . "<br />";
    
    //Name
    $this->Name = $this->Race->generateHeroName();
    echo "Name: " . $this->Name . "<br />";
    
    //Attributes
    echo "Str ";
    $this->Str = $this->GenerateAtribute($this->Race->StrBon);//include bonuses argument and level argument
    echo "Dex ";
    $this->Dex = $this->GenerateAtribute($this->Race->DexBon);
    echo "Con ";
    $this->Con = $this->GenerateAtribute($this->Race->ConBon);
    echo "Int ";
    $this->Intel = $this->GenerateAtribute($this->Race->IntelBon);
    echo "Wis ";
    $this->Wis = $this->GenerateAtribute($this->Race->WisBon);
    echo "Cha ";
    $this->Cha = $this->GenerateAtribute($this->Race->ChaBon);
    echo "Fte ";
    $this->Fte = $this->GenerateAtribute($this->Race->FteBon);
    
    //Class
    $this->HeroClass = $this->GenerateHeroClass();
    echo "<br />Class: " . $this->HeroClass->Name . " HD: D" . $this->HeroClass->HD . "<br />";
    
    //HP
    $this->MaxHP = $this->HeroClass->HD + $this->calculateAttributeBonus($this->Con);  //base the multiplyer on HD and con
    $this->CurrentHP = $this->MaxHP;
    echo "Hit Points: " . $this->CurrentHP . "/" . $this->MaxHP . "<br />";
    
    //Level
    $this->Level = 1;
    echo "Level:" . $this->Level . "<br />";
    
    //XP
    $XPBonus = rand(0, $this->Fte);
    $this->CurrentXP = 0;
    $this->LevelUpXP = 100 - $XPBonus;
    echo "XP: " . $this->CurrentXP . "/" . $this->LevelUpXP . " Luck Bonus: " . $XPBonus . "<br />";
    
    //check for levelup
    if($level > 1)
    {
      $i=0;
      while($i < $level - 1)
      {
        if($this->forceLevelUP())//add in try catch, make forceLevelUP throw error when reached class cap
        {
          $i++;
        }
        else
        {
          break;
        }
      }
    }
    
    //generate weapon
  }
  
  function GiveToUser($UID)
  {
    //check user exists
    $this->OwnerID = $UID;
  }
  
  function addXP($XP)
  {
    echo "Current XP: " . $this->CurrentXP . "<br />";
    echo "Adding 1,000XP<br />";
    $this->CurrentXP += $XP;//add the XP
    
    if($this->CurrentXP > $this->LevelUpXP)//if its more then the level up reduce it to the levelup amount
    {
      $this->CurrentXP = $this->LevelUpXP;
    }
    
    echo "New XP: " . $this->CurrentXP . "<br />";
  }
  
  function levelUP()
  {
    //returns true if it worked
    if($this->CurrentXP == $this->LevelUpXP)//we have enough XP
    {
      $this->forceLevelUP();
      return true;
    }
    else
    {
      echo "Not Enough XP to level UP!<br />";
      return false;
    }
  }
  
  function forceLevelUP()//same as level up without the checks, used in character gen when XP shouldnt get in the way
  {
    if($this->Level == $this->HeroClass->LevelCap)//check if class is at Level cap
    {
      echo "<br />At " . $this->HeroClass->Name . " level cap, Trying to find new class<br />";
      if(!$this->HeroClass->checkForNewClass($this))
      {
        //no new class. have reached level cap.
        echo "Dont meet the requirements for any classes. :(<br /><br /><br />";
        //cant levelup, we are done here.
        return false;
      }
      else
      {
        //we found a new class and have applied it. now we can level
        echo "Have chosen class: " . $this->HeroClass->Name . "<br />";
      }
      //search for new class
    }
    //not at level cap, all good to continue
    
    //increase level
    $this->Level += 1;
    echo "<br /><br /><strong>Leveling to " . $this->Level . "</strong><br />";
    
    //add hp
    $extraHP = rand(1,$this->HeroClass->HD) + $this->calculateAttributeBonus($this->Con);
    if($extraHP < 1)//minimum 1 hitpoint increase.
    {
      $extraHP = 1;
    }
    $this->MaxHP += $extraHP;
    $this->CurrentHP = $this->MaxHP; //healed when leveled up?? could be exploited....
    echo "Adding " . $extraHP . " HP. Rolled a d" . $this->HeroClass->HD . "+" . $this->calculateAttributeBonus($this->Con) . "<br />";
    
    //increase XP cap
    $this->CurrentXP = $this->LevelUpXP;
    //how much bonus do they already have?
    $currentXPBonus = (100 * pow($this->Level -1, 2)) - $this->LevelUpXP;
    //calc new bonus
    $newXPBonus = 0;
    $i=0;
    while($i < $this->Level)
    {
      $newXPBonus += rand(0, $this->Fte); //level D fate (1d6 -> foo D bar)
      $i++;
    }
    $this->LevelUpXP = (100 * pow($this->Level, 2)) - $currentXPBonus - $newXPBonus;
    echo "New XP cap:" . $this->LevelUpXP . " XP Bonus this Level: " . $newXPBonus . "<br />";
        
    //increase 1 attribute
    $possibleAttribute = array("Str", "Dex", "Con", "Intel", "Wis", "Cha", "Fte");//dynamiclly create this array using a Class favoured Attribute, weighted with Fate
    if($this->calculateAttributeBonus($this->Fte) > 0)//add favoured bonus to array, for each fate bonus above 0
    {
      $i=0;
      while($i < $this->calculateAttributeBonus($this->Fte))
      {
        array_push($possibleAttribute, $this->HeroClass->FavouredAttribute);
        $i++;
      }
    }
    print_r($possibleAttribute);
    $pickAttribute = $possibleAttribute[rand(0, count($possibleAttribute) -1)];
    if      ($pickAttribute == "Str") {$this->Str++; echo "<b>Increase Str</b><br />";}
    else if($pickAttribute == "Dex") {$this->Dex++; echo "<b>Increase Dex</b><br />";}
    else if($pickAttribute == "Con") {$this->Con++; echo "<b>Increase Con</b><br />";}
    else if($pickAttribute == "Intel") {$this->Intel++; echo "<b>Increase Intel</b><br />";}
    else if($pickAttribute == "Wis") {$this->Wis++; echo "<b>Increase Wis</b><br />";}
    else if($pickAttribute == "Cha") {$this->Cha++; echo "<b>Increase Cha</b><br />";}
    else if($pickAttribute == "Fte") {$this->Fte++; echo "<b>Increase Fte</b><br />";}
    
    return true;
  }
  
  function calculateAttributeBonus($attribute)
  {
    $bonus = floor(($attribute - 10) / 2);
    
    return $bonus;
  }
  
  function getAttributeByName($name)
  {
	if ($name == "Str")
      return $this->Str;
	else if ($name == "Dex")
	  return $this->Dex;
	else if ($name == "Con")
	  return $this->Con;
	else if ($name == "Intel")
	  return $this->Intel;
	else if ($name == "Wis")
	  return $this->Wis;
	else if ($name == "Cha")
	  return $this->Cha;
	else if ($name == "Fte")
	  return $this->Fte;
	else
	  throw new Exception("Not a known attribute: $name");
  }
  
  function calcDamage()
  {
	$weapon = Weapon::loadWeapon($this->WeaponID);
	$attr   = $this->getAttributeByName($weapon->DamageAttribute);
	$bonus  = $this->calculateAttributeBonus($attr);
	$damage = $weapon->calcDamage($this->calculateAttributeBonus($this->Fte), $bonus);
	
	return $damage;
  }
  
  function takeDamage($damage)
  {
	$damage -= $this->calculateAttributeBonus($this->Dex);//dodge Damage Reduction
	//$damage -= ARMOR;//Armor Damage Deduction
	
	if($damage < 1)//always take at least 1 damage
	{
		$damage = 1;
	}
	
	$this->CurrentHP -= $damage;
	
	return $damage;
  }
  
  function rollInitiative()
  {
    return rand(1,20) + $this->calculateAttributeBonus($this->Wis);
  }
  
  function GenerateAtribute($bonus)
  {  
    //shitty 4d6 drop 1
    $diceRolled = array(rand(1, 6), rand(1, 6), rand(1, 6), rand(1, 6));
    rsort($diceRolled);//Sort the 4d6
    
    echo "Attribute Dice: " . $diceRolled[0] . ", " . $diceRolled[1] . ", " . $diceRolled[2] . " +" . $bonus . " Drop: " . $diceRolled[3] . " ";
    
    $attribute = $diceRolled[0] + $diceRolled[1] + $diceRolled[2] + $bonus;//add the highest 3 and the bonus passed in
    echo "<strong>Total: " . $attribute . " Bonus: " . $this->calculateAttributeBonus($attribute) . "</strong><br />";

    return $attribute;
  }
  
  
  function GenerateHeroClass()//all characters start as commoners
  {
    //change to load(by name "commoner")
    $commoner = HeroClass::loadHeroClass(1);//1 is the ID of commoner. hacky!

    return $commoner;
  }
  
  
  function GenerateRace()
  {
    //This is pretty terrible but it matches the DB for now
    $human = Race::loadRace(1);
    $dwarf = Race::loadRace(2);
    $elf = Race::loadRace(3);
    $halfling = Race::loadRace(4);
    
    $races = array($human, $dwarf, $elf, $halfling);
    
    $newRace = $races[rand(0,3)];
    
    return $newRace;
  }
  
  function SaveHero()//could be called just Save()??
  {
    //check shit is ok
    
    //if $ID !== null, update
    
    if($this->ID != null)
    {
      $updateQuery = "UPDATE `kr00ny_sf`.`Hero` SET 
                               `OwnerID` = " . $this->OwnerID . ", 
                               `PartyID` = " . $this->PartyID . ", 
                               `Name` = '" . $this->Name . "',  
                               `Race` = " . $this->Race->ID . ",          
                               `Class` = " . $this->HeroClass->ID . ",    
                               `MaxHP` = " . $this->MaxHP . ",
                               `CurrentHP` = " . $this->CurrentHP . ",
                               `Level` = " . $this->Level . ",
                               `CurrentXP` = " . $this->CurrentXP . ",
                               `LevelUpXP` = " . $this->LevelUpXP . ",
                               `Str` = " . $this->Str . ",
                               `Dex` = " . $this->Dex . ",
                               `Con` = " . $this->Con . ",
                               `Intel` = " . $this->Intel . ",
                               `Wis` = " . $this->Wis . ",
                               `Cha` = " . $this->Cha . ",
                               `Fte` = " . $this->Fte . ",
                               `WeaponID` = " . $this->WeaponID . "
                               WHERE `Hero`.`ID` = " . $this->ID . ";";
      echo "Updating Hero: " . $updateQuery . "<br />";
      mysql_query($updateQuery);
    }
    else //no id, add new character
    {
      $InsertQuery = "INSERT INTO `Hero` (`OwnerID`,                  `PartyID`, `Name`,            `Race`,                  `Class`,                    `MaxHP`,            `CurrentHP`,            `Level`,            `CurrentXP`,            `LevelUpXP`,            `Str`,            `Dex`,            `Con`,            `Intel`,          `Wis`,            `Cha`,            `Fte`,            `WeaponID`
                                ) VALUES ('".$this->OwnerID."',       '0',       '".$this->Name."', '".$this->Race->ID."', '".$this->HeroClass->ID ."', '".$this->MaxHP."', '".$this->CurrentHP."', '".$this->Level."', '".$this->CurrentXP."', '".$this->LevelUpXP."', '".$this->Str."', '".$this->Dex."', '".$this->Con."', '".$this->Intel."', '".$this->Wis."', '".$this->Cha."', '".$this->Fte."', '0');";
      echo "Inserting New Hero: " . $InsertQuery . "<br />";
      mysql_query($InsertQuery);
    }
    
    //some sort of try catch error detection
  }
  
  function GetAllHeros()
  {
    //return array of all adventurers in DB
    //NOT THIS CLASS JOB thats for the controller
  }
  
}

?>
