<?php 
// Object being stored in session
// Contains variables (+ getters and setters) that needs to be saved between page reloads
class CDiceSession {
  private $diceArray;
  private $rounds = 0;
  private $sum = 0;
  private $totalSum = 0;
  
  public function GetDiceArray() {
    return $this->diceArray;      
  }
  
  public function SetDiceArray(array $diceArray) {
    $this->diceArray = $diceArray;
    // Checks if the first roll in the array is "1", if it is reset the array
    if(current($this->diceArray) === 1) {
      $this->diceArray = array();     
    }
  }
  
  public function GetRounds() {
    return $this->rounds;        
  }
  
  public function SetRounds($rounds) {
    $this->rounds = $rounds;        
  }
  
  public function GetSum() {
    return $this->sum;        
  }
  
  public function SetSum($sum) {
    $this->sum = $sum;        
  }
  
  public function GetTotalSum() {
    return $this->totalSum;        
  }
  
  public function SetTotalSum($totalSum) {
    $this->totalSum = $totalSum;        
  }
}
