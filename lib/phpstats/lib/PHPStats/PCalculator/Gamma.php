<?php
namespace PHPStats\PCalculator;

/**
* PHP Statistics Library
*
* Copyright (C) 2011-2012 Michael Cordingley<Michael.Cordingley@gmail.com>
* 
* This library is free software; you can redistribute it and/or modify
* it under the terms of the GNU Library General Public License as published
* by the Free Software Foundation; either version 3 of the License, or 
* (at your option) any later version.
* 
* This library is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Library General Public
* License for more details.
* 
* You should have received a copy of the GNU Library General Public License
* along with this library; if not, write to the Free Software Foundation, 
* Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
* 
* LGPL Version 3
*
* @package PHPStats
*/

use PHPStats\Exception as PHPStatsException,
	PHPStats\BasicStats,
	PHPStats\Generator\GeneratorInterface;

/**
* Gamma class
* 
* Represents the gamma distribution, a generalization of the exponential,
* chi-square, and Erlang distributions.  When k is an integer, then this
* is the Erlang distribution, which represents the sum of k independent
* exponentially distributed random variables.
* 
* For more information, see: http://en.wikipedia.org/wiki/Gamma_distribution
*
* @author Michael Cordingley <Michael.Cordingley@gmail.com>
* @since 0.0.4
*/
class Gamma extends BasicCalculator
{
    
    /**
     * Returns a random float between $minimum and $minimum plus $maximum
     * 
     * @param float $k Shape parameter
     * @param float $theta Scale parameter
     * @return float The random variate.
     */
    public function getRvs($k = 1, $theta = 1)
    {
	$floork        = floor($k);
	$fractionalk   = $k - $floork;
	$xi            = 0;
	$sumLogUniform = 0;
	
	for ($index = 1; $index <= $floork; $index++) {
	    $sumLogUniform += log($this->randFloat());
	}
    
	if ($fractionalk > 0) {
	    $m = 0;
	    $V = array(0);
	    do {
		$m++;

		$V[] = $this->randFloat();
		$V[] = $this->randFloat();
		$V[] = $this->randFloat();

		if ($V[3*$m - 2] <= M_E/(M_E + $fractionalk)) {
		    $xi = pow($V[3*$m - 1], 1/$fractionalk);
		    $eta = $V[3*$m]*pow($xi, $fractionalk - 1);
		}
		else {
		    $xi = 1 - log($V[3*$m - 1]);
		    $eta = $V[3*$m]*exp(-$xi);
		}
		
	    } while($eta > pow($xi, $fractionalk - 1)*exp(-$xi));
	}

	return $theta*($xi - $sumLogUniform);
    }
    
    /**
     * Returns the probability distribution function
     * 
     * @param float $x The test value
     * @param float $k Shape parameter
     * @param float $theta Scale parameter
     * @return float The probability
     */
    public function getPdf($x, $k = 1, $theta = 1)
    {
	return pow($x, $k - 1) * exp(-$x/$theta) / ($this->basic->gamma($k) * pow($theta, $k));
    }
    
    /**
     * Returns the cumulative distribution function, the probability of getting the test value or something below it
     * 
     * @param float $x The test value
     * @param float $k Shape parameter
     * @param float $theta Scale parameter
     * @return float The probability
     */
    public function getCdf($x, $k = 1, $theta = 1)
    {
	return $this->basic->lowerGamma($k, $x/$theta) / $this->basic->gamma($k);
    }
    
    /**
     * Returns the survival function, the probability of getting the test value or something above it
     * 
     * @param float $x The test value
     * @param float $k Shape parameter
     * @param float $theta Scale parameter
     * @return float The probability
     */
    public function getSf($x, $k = 1, $theta = 1)
    {
	return 1.0 - $this->getCdf($x, $k, $theta);
    }
    
    /**
     * Returns the percent-point function, the inverse of the cdf
     * 
     * @param float $x The test value
     * @param float $k Shape parameter
     * @param float $theta Scale parameter
     * @return float The value that gives a cdf of $x
     */
    public function getPpf($x, $k = 1, $theta = 1)
    {
	return $theta * $this->basic->ilowerGamma($k, $this->basic->gamma($k) * $x);
    }
    
    /**
     * Returns the inverse survival function, the inverse of the sf
     * 
     * @param float $x The test value
     * @param float $k Shape parameter
     * @param float $theta Scale parameter
     * @return float The value that gives an sf of $x
     */
    public function getIsf($x, $k = 1, $theta = 1)
    {
	return $this->getPpf(1.0 - $x, $k, $theta);
    }
    
    /**
     * Returns the moments of the distribution
     * 
     * @param string $moments Which moments to compute. m for mean, v for variance, s for skew, k for kurtosis.  Default 'mv'
     * @param float $k Shape parameter
     * @param float $theta Scale parameter
     * @return type array A dictionary containing the first four moments of the distribution
     */
    public function getStats($moments = 'mv', $k = 1, $theta = 1)
    {
	$return = array();
	
	if (strpos($moments, 'm') !== FALSE) $return['mean'] = $k*$theta;
	if (strpos($moments, 'v') !== FALSE) $return['variance'] = $k*pow($theta, 2);
	if (strpos($moments, 's') !== FALSE) $return['skew'] = 2/sqrt($k);
	if (strpos($moments, 'k') !== FALSE) $return['kurtosis'] = 6/$k;
	
	return $return;
    }
}
/* End of File */
