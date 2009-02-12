<?php
interface iOrder {
  public function getId();
  public function retrieveById($value);
  public function success($transcript);
  public function error($err_code);
  public function getAmount();
}