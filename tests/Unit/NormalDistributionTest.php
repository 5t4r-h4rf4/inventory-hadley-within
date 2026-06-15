<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\CalculationController;
use ReflectionMethod;

class NormalDistributionTest extends TestCase
{
    private function invokeMethod($name, array $parameters = [])
    {
        $controller = new CalculationController();
        $method = new ReflectionMethod(CalculationController::class, $name);
        $method->setAccessible(true);
        return $method->invokeArgs($controller, $parameters);
    }

    /**
     * Test standard normal PDF function.
     */
    public function test_std_normal_pdf()
    {
        $pdf0 = $this->invokeMethod('stdNormalPdf', [0.0]);
        $this->assertEqualsWithDelta(0.3989422804, $pdf0, 0.0001);

        $pdf1 = $this->invokeMethod('stdNormalPdf', [1.0]);
        $this->assertEqualsWithDelta(0.2419707245, $pdf1, 0.0001);

        $pdf2 = $this->invokeMethod('stdNormalPdf', [-1.0]);
        $this->assertEqualsWithDelta(0.2419707245, $pdf2, 0.0001);
    }

    /**
     * Test standard normal CDF function.
     */
    public function test_std_normal_cdf()
    {
        $cdf0 = $this->invokeMethod('stdNormalCdf', [0.0]);
        $this->assertEqualsWithDelta(0.5, $cdf0, 0.0001);

        $cdf1_96 = $this->invokeMethod('stdNormalCdf', [1.96]);
        $this->assertEqualsWithDelta(0.975, $cdf1_96, 0.001);

        $cdf_neg1_96 = $this->invokeMethod('stdNormalCdf', [-1.96]);
        $this->assertEqualsWithDelta(0.025, $cdf_neg1_96, 0.001);
    }

    /**
     * Test standard normal Inverse CDF function.
     */
    public function test_std_normal_inverse_cdf()
    {
        $inv0_5 = $this->invokeMethod('stdNormalInverseCdf', [0.5]);
        $this->assertEqualsWithDelta(0.0, $inv0_5, 0.0001);

        $inv0_975 = $this->invokeMethod('stdNormalInverseCdf', [0.975]);
        $this->assertEqualsWithDelta(1.96, $inv0_975, 0.01);

        $inv0_025 = $this->invokeMethod('stdNormalInverseCdf', [0.025]);
        $this->assertEqualsWithDelta(-1.96, $inv0_025, 0.01);
    }
}
