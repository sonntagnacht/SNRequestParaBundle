<?php

namespace SN\RequestParamBundle\Tests\Request;

use SN\RequestParamBundle\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class RequestParameterTest extends BaseTestCase
{

    public function testInvalidOptions()
    {
        $sampleRequest = new SampleOptionalIntRequest();

        try {
            $sampleRequest->resolve(array('optionalInt' => true));
        } catch (InvalidOptionsException $e) {
            $this->assertInstanceOf(InvalidOptionsException::class, $e);
        }
    }

    public function testOptionalIntParams()
    {
        $sampleRequest = new SampleOptionalIntRequest();
        $sampleRequest->resolve(array(
            'optionalInt'         => 1,
            'optionalIntDefault3' => 10,
            'optionalNegativeInt' => -1
        ));

        $this->assertEquals(1, $sampleRequest->getOptionalInt());
        $this->assertEquals(10, $sampleRequest->getOptionalIntDefault3());
        $this->assertEquals(-1, $sampleRequest->getOptionalNegativeInt());

        // force override options
        $sampleRequest->resolve(array(
            'optionalInt'         => 2,
            'optionalIntDefault3' => 10,
            'optionalNegativeInt' => -1
        ));
        $optionsUpdated = $sampleRequest->getOptions(true);
        $this->assertNotEquals(
            array(
                'optionalInt'         => 1,
                'optionalIntDefault3' => 10,
                'optionalNegativeInt' => -1
            ),
            $optionsUpdated
        );
        $this->assertEquals(
            array(
                'optionalInt'         => 2,
                'optionalIntDefault3' => 10,
                'optionalNegativeInt' => -1
            ),
            $optionsUpdated
        );
    }

    public function testMandatoryIntParams()
    {
        $sampleRequest = new SampleMandatoryIntRequest();
        $sampleRequest->resolve(array(
            'mandatoryInt'         => 33,
            'mandatoryIntDefault3' => null,
            'mandatoryNegativeInt' => -33
        ));

        $this->assertEquals(
            array(
                'mandatoryInt'         => 33,
                'mandatoryIntDefault3' => null,
                'mandatoryNegativeInt' => -33
            ),
            $sampleRequest->getOptions()
        );
    }

    public function testBoolParams()
    {
        $sampleRequest = new SampleBoolRequest();
        $sampleRequest->resolve(array(
            'optionalBool'  => true,
            'mandatoryBool' => true
        ));

        $allowedTypes  = SampleBoolRequest::getAllowedBooleanTypes();
        $allowedValues = SampleBoolRequest::getAllowedBooleanValues();
        $this->assertEquals(true, is_array($allowedTypes));
        $this->assertEquals(true, is_array($allowedValues));

        foreach ($allowedValues as $value) {
            $valueSampleRequest = new SampleBoolRequest(array(
                'mandatoryBool' => $value
            ));
            $this->assertEquals(SampleBoolRequest::normalizeBoolean($value), $valueSampleRequest->getMandatoryBool());
        }

    }

    public function testNelmioApiDocDefaults()
    {
        $sampleRequest = new SampleAPIDocRequest();
        $sampleRequest->resolve(array());
        $this->assertClassHasAttribute('_format', SampleAPIDocRequest::class);

        $sampleRequest = new SampleAPIDocRequest(array(
            '_format' => 'json'
        ));
        $this->assertAttributeEquals('json', '_format', $sampleRequest);

        $request = new Request(array(
            '_format' => 'json'
        ));
        $request->attributes->set('_route_params', array());
        $sampleRequest = new SampleAPIDocRequest($request);
        $this->assertAttributeEquals('json', '_format', $sampleRequest);
        $this->assertEquals(array('_format' => 'json'), $sampleRequest->getOptions());
        $this->assertEquals(array('_format' => 'json'), $sampleRequest->getOptions(true));

        try {
            new SampleAPIDocRequest(array(
                '_format' => 'xml'
            ));
        } catch (InvalidOptionsException $e) {
            $this->assertInstanceOf(InvalidOptionsException::class, $e);
        }
    }

    public function testPaginatedParams()
    {
        $sampleRequest = new SamplePaginatedRequest();
        $sampleRequest->resolve(array());

        $sampleRequest = new SamplePaginatedRequest(array(
            'page'  => 1,
            'limit' => 100
        ));
        $this->assertEquals(1, $sampleRequest->getPage());
        $this->assertEquals(100, $sampleRequest->getLimit());

        $sampleRequest = new SamplePaginatedRequest(array(
            'page'  => 1,
            'limit' => 500
        ));
        $this->assertEquals(1, $sampleRequest->getPage());
        $this->assertNotEquals(500, $sampleRequest->getLimit());
        // default limit is set to 25
        $this->assertEquals(25, $sampleRequest->getLimit());
    }

    public function testStringParams()
    {
        $sampleRequest = new SampleStringRequest();
        $sampleRequest->resolve(array(
            'optionalString'  => null,
            'mandatoryString' => "abc"
        ));

        $this->assertEquals(
            array(
                'optionalString'  => null,
                'mandatoryString' => "abc",
                'defaultString'   => "abc"
            ),
            $sampleRequest->getOptions()
        );

    }

    public function testStringList()
    {
        $sampleRequest = new SampleStringListRequest();
        $sampleRequest->resolve(array(
            'optionalStringList'  => ["x", "y", "z"],
            'mandatoryStringList' => ["a", "b", "c"],
            'notStringList'       => "d",
        ));

        $this->assertEquals(
            array(
                'optionalStringList'  => ["x", "y", "z"],
                'mandatoryStringList' => ["a", "b", "c"],
                'notStringList'       => "d"
            ),
            $sampleRequest->getOptions()
        );
    }

    public function testIdList()
    {
        $sampleRequest = new SampleIdListRequest();
        $sampleRequest->resolve(array(
            'optionalIdList'  => [1, 2, 3],
            'mandatoryIdList' => [9, 8, 7]

        ));

        $this->assertEquals(
            array(
                'optionalIdList'  => [1, 2, 3],
                'mandatoryIdList' => [9, 8, 7]

            ),
            $sampleRequest->getOptions()
        );
    }

    public function testFormat()
    {
        $sampleRequest = new SampleAPIDocRequest();

        $sampleRequest->_setFormat("text");
        $this->assertEquals("text", $sampleRequest->_getFormat());
    }

//    public function testDateParam()
//    {
//        $date = new \DateTime();
//
//        $sampleRequest = new SampleDateRequest();
//        $sampleRequest->resolve(array(
//            'date' => $date->format(\DateTime::ISO8601)
//        ));
//
//        $this->assertEquals(
//            array(
//                'date' => $date
//            ),
//            $sampleRequest->getOptions()
//        );
//    }


}