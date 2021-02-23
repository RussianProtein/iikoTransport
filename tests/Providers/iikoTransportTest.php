<?php
/**
 * 02/09/2020
 * @author Sergey Borguronov <multiatlast@gmail.com>
 */

namespace Tests\Providers;

use Tests\BaseTestCase;
use RussianProtein\iikoTransport\iikoTransport;

class iikoTransportTest extends BaseTestCase
{

    public function testGetToken()
    {
        $iiko = new iikoTransport();
        
        $this->assertIsString($iiko->getToken());
    }

    public function testGetOrganizations()
    {
        $iiko = new iikoTransport();

        $organizations = $iiko->getOrganizations(null, true, true);

        $this->assertIsObject($organizations);

        return $organizations;
    }


    /**
     * @depends testGetOrganizations
     */
    public function testGetNomenclature(object $organizations)
    {
        foreach($organizations->organizations as $val){
            $res[] = $val->id;
        }
        
        $this->assertIsArray($res);
    }
    
}