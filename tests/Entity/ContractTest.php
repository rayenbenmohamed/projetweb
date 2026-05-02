<?php

namespace App\Tests\Entity;

use App\Entity\Contract;
use PHPUnit\Framework\TestCase;

class ContractTest extends TestCase
{
    public function testSalaryCalculation(): void
    {
        $contract = new Contract();
        
        // Test with 1000 TND
        $contract->setSalary(1000);
        
        // 1000 * 0.82 = 820
        $this->assertEquals(820, $contract->getSalaireNet());
    }

    public function testSalaryCalculationZero(): void
    {
        $contract = new Contract();
        $contract->setSalary(0);
        
        $this->assertEquals(0, $contract->getSalaireNet());
    }

    public function testStatusDefaultValue(): void
    {
        $contract = new Contract();
        $this->assertEquals('En Attente', $contract->getStatus());
    }
}
