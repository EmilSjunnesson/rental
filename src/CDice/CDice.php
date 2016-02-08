<?php 
class CDice {
  private $roll;
  
  // Rolls the dice
  public function Roll() {
    $this->roll = rand(1, 6);
  }
  
  // Returns the value of the roll
  public function GetRollScore() {
    return $this->roll;      
  }
}
