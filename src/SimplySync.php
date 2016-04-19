<?php

namespace Lengieng\SimplySync;

require_once 'vendor\SimplyCast\php-wrapper\SimplyCastAPI.php';
require_once 'ActEssential.php';
require_once 'Freshdesk.php';
require_once 'SalesforceCRM.php';
require_once 'Solve360.php';
require_once 'TactileCRM.php';

use SimplyCast;

class SimplySync
{
    /**
     * Constant value for Act Essential.
     * @var string
     */
    const PLATFORM_ACTESSENTIAL = 'actessential';

    /**
     * Constant value for Freshdesk.
     * @var string
     */
    const PLATFORM_FRESHDESK = 'freshdesk';

    /**
     * Constant value for Salesforce CRM.
     * @var string
     */
    const PLATFORM_SALESFORCECRM = 'salesforcecrm';

    /**
     * Constant value for Solve360.
     * @var string
     */
    const PLATFORM_SOLVE360 = 'solve360';

    /**
     * Constant value for Tactile CRM.
     * @var string
     */
    const PLATFORM_TACTILECRM = 'tactilecrm';

    /**
     * Constant value for platform.
     * @var string
     */
    const PLATFORMS = [
        self::PLATFORM_ACTESSENTIAL,
        self::PLATFORM_FRESHDESK,
        self::PLATFORM_SALESFORCECRM,
        self::PLATFORM_SOLVE360,
        self::PLATFORM_TACTILECRM,
    ];

    /**
     * SimplyCast API handler.
     * @var object
     */
    private $scHandler;

    /**
     * API handler for all the platforms.
     * @var object[]
     */
    private $plfmHandlers = array();

    /**
     * Constructor
     * @param TODO
     */
//    public function __construct($plfmHandlers = array())
//    {
//        $hasInvalidObj = false;
//        for ($i = 0; $i < count($plfmHandlers); $i++) {
//            if ($plfmHandlers[$i] instanceof IRestfulConnection) {
//                if (!$hasInvalidObj) {
//                    $this->plfmHandlers[] = $plfmHandlers[$i];
//                }
//            } else {
//                $hasInvalidObj = true;
//                if (!isset($invalidClasses)) {
//                    $invalidClasses = array();
//                }
//                $invalidClasses[] = get_class($plfmHandlers[$i]);
//            }
//        }
//
//        // Cannot continue without valid classes
//        if ($hasInvalidObj) {
//            $errorMsg = __FUNCTION__ . ": invalid platform handler(s):";
//            for ($i = 0; $i < count($invalidClasses); $i++) {
//                $errorMsg .= $i == 0 ? " " : ", ";
//                $errorMsg .= "{$invalidClasses[$i]}";
//            }
//            $errorMsg .= ".";
//            throw new \Exception($errorMsg);
//        }
//    }

    /**
     * Return an array of platform handlers.
     *
     * @return array   An array of platform handlers.
     */
    public function getPlfmHandlers()
    {
        return $this->plfmHandlers;
    }

    /**
     * Return a specific platform handler based on the given platform name.
     *
     * @param string $platform  Platform name.
     *
     * @return object   A handler of the requested platform.
     */
    public function getPlfmHandler($platform)
    {
        if (array_key_exists($platform, $this->plfmHandlers)) {
            return $this->plfmHandlers[$platform];
        }

        return null;
    }

    /**
     * Setup SimplyCast API.
     *
     * @param string $publicKey The public key.
     * @param string $secretKey The secret key.
     *
     * @return object   SimplyCast API handler.
     */
    public function setupSimplyCast($publicKey, $secretKey)
    {
        $this->scHandler = new \SimplyCast\API($publicKey, $secretKey);
        return $this->scHandler;
    }

    /**
     * Instantiate ActEssential with the given params.
     *
     * @param array $params     Key/value pairs array containing necessary
     *  arguments for setting up ActEssential instance.
     *
     * @return object   An instance of ActEssential if successful;
     *  Otherwise, false.
     */
    private function getActEssentialInstance($params)
    {
        if (isset($params['apikey']) && isset($params['devkey'])) {
            $secure = isset($params['secure']) ? $params['secure'] : true;
            $obj = new ActEssential($params['apikey'], $params['devkey'], $secure);
            return $obj;
        }
        return false;
    }

    /**
     * Instantiate Freshdesk with the given params.
     *
     * @param array $params     Key/value pairs array containing necessary
     *  arguments for setting up Freshdesk instance.
     *
     * @return object   An instance of Freshdesk if successful;
     *  Otherwise, false.
     */
    private function getFreshdeskInstance($params)
    {
        if (isset($params['domain'])) {
            if (isset($params['apikey'])) {
                $obj = new Freshdesk($params['domain'], $params['apikey']);
            } elseif (isset($params['id']) && isset($params['password'])) {
                $obj = new Freshdesk($params['domain'], $params['id'], $params['password']);
            }
            return $obj;
        }
        return false;
    }

    /**
     * Instantiate SalesforceCRM with the given params.
     *
     * @param array $params     Key/value pairs array containing necessary
     *  arguments for setting up SalesforceCRM instance.
     *
     * @return object   An instance of SalesforceCRM if successful;
     *  Otherwise, false.
     */
    private function getSalesforceCRMInstance($params)
    {
        if (isset($params['id']) && isset($params['secret']) && isset($params['redirectURI'])) {
            $obj = new SalesforceCRM($params['id'], $params['secret'], $params['redirectURI']);
            return $obj;
        } elseif (isset($params['id']) && isset($params['secret']) &&
                  isset($params['username']) && isset($params['password']) &&
                  isset($params['token'])) {
            $obj = new SalesforceCRM(
                $params['id'],
                $params['secret'],
                $params['username'],
                $params['password'],
                $params['token']
            );

            // Access token is saved here.
            $obj->getOAuthTokenByPassword();
            return $obj;
        }
        return false;
    }

    /**
     * Instantiate Solve360 with the given params.
     *
     * @param array $params     Key/value pairs array containing necessary
     *  arguments for setting up Tactile CRM instance.
     *
     * @return object   An instance of Solve360 if successful;
     *  Otherwise, false.
     */
    private function getSolve360Instance($params)
    {
        if (isset($params['email']) && isset($params['token'])) {
            $secure = isset($params['secure']) ? $params['secure'] : true;
            $obj = new Solve360($params['email'], $params['token'], $secure);
            return $obj;
        }
        return false;
    }

    /**
     * Instantiate Tactile CRM with the given params.
     *
     * @param array $params     Key/value pairs array containing necessary
     *  arguments for setting up Tactile CRM instance.
     *
     * @return object   An instance of Tactile CRM if successful;
     *  Otherwise, false.
     */
    private function getTactileCRMInstance($params)
    {
        if (isset($params['url']) && isset($params['token'])) {
            $secure = isset($params['secure']) ? $params['secure'] : true;
            $obj = new TactileCRM($params['url'], $params['token'], $secure);
            return $obj;
        }
        return false;
    }

    /**
     * Instantiate the specified platform with the given params.
     *
     * @param string $platform  Supported platform name.
     * @param array $params     Key/value pairs array containing necessary
     *  arguments for setting up each platform instance.
     *
     * @return object   An instance of the platform.
     */
    private function getPlatformInstance($platform, $params)
    {
        switch ($platform) {
            case self::PLATFORM_ACTESSENTIAL:
                $obj = $this->getActEssentialInstance($params);
                break;
            case self::PLATFORM_FRESHDESK:
                $obj = $this->getFreshdeskInstance($params);
                break;
            case self::PLATFORM_SALESFORCECRM:
                $obj = $this->getSalesforceCRMInstance($params);
                break;
            case self::PLATFORM_SOLVE360:
                $obj = $this->getSolve360Instance($params);
                break;
            case self::PLATFORM_TACTILECRM:
                $obj = $this->getTactileCRMInstance($params);
                break;
            default:
                throw new \Exception(__FUNCTION__ .
                                     ': invalid or unsupported platform.');
        }

        if (is_object($obj)) {
            return $obj;
        }

        throw new \Exception(__FUNCTION__ . ': invalid arguments.');
    }

    /**
     * Setup platform instance.
     *
     * @param string $platform  Supported platform name.
     * @param array $params     Key/value pairs array containing necessary
     *  arguments for setting up each platform instance.
     *
     * @return object   An instance of the platform.
     */
    public function setup($platform, $params)
    {
        // Check whether the platform is supported.
        $this->isSupported($platform, true);

        if (!is_array($params)) {
            throw new \Exception(__FUNCTION__ . ': params must be an array.');
        }

        $this->plfmHandlers[$platform] = $this->getPlatformInstance($platform, $params);
        return $this->plfmHandlers[$platform];
    }

    /**
     * Check if platform is supported.
     *
     * @param string $platform  Platform name.
     * @param boolean $reportError  true if an exception is to be thrown on
     *  error.
     *
     * @return boolean  true if the specified platform is supported.
     */
    public function isSupported($platform, $reportError = true)
    {
        $status = in_array($platform, self::PLATFORMS, true);

        if ($reportError && $status === false) {
            if (!$this->isSupported($platform)) {
                throw new \Exception(__FUNCTION__ . ': platform not supported.' .
                                    ' Supported platforms are ' .
                                     implode(", ", self::PLATFORMS) . '.');
            }
        }
        return $status;
    }

    /**
     * Normalize the data fields.
     *
     * @param array $contacts   An array of contact object.
     *
     * @return  array   An array of contacts with fields identifiable by
     *  SimplyCast CRM.
     */
    public function normalize($contacts)
    {
        $normalizeFile = __DIR__ . '\normalize.json';
        $newContacts = array();
        $normString = file_get_contents($normalizeFile, false);
        if ($normString === false) {
            throw new \Exception("Failed to read {$normalizeFile} file. Make" .
                                 " sure the file exists in the 'src' directory.");
        }

        $fields = json_decode($normString);
        for ($i = 0; $i < count($contacts); $i++) {
            $nameIndex = -1;
            $firstname = null;
            $lastname = null;
            $name = null;
            foreach ($contacts[$i] as $key => $val) {
                if ($val === null) {
                    continue;
                }
                foreach ($fields as $field) {
                    if (count($field->keyword) > 0) {
                        if (is_array($val) || is_object($val)) {
                            // In case there is a sub-field, we need to check
                            // all of them. "MailingAddress" in SalesforceCRM
                            // is a good example of this one.
                            foreach ($val as $subFieldKey => $subFieldVal) {
                                if (is_object($subFieldVal) ||
                                    is_array($subFieldVal) ||
                                    $subFieldVal === null) {
                                    continue;
                                }

                                if (in_array(strtolower($subFieldKey), $field->keyword)) {
                                    $newContacts[$i][] = array(
                                        "id" => "{$field->id}",
                                        "value" => "{$subFieldVal}",
                                    );
                                    break;
                                }
                            }
                        } else {
                            if (in_array(strtolower($key), $field->keyword)) {
                                if ($nameIndex > -1) {
                                    // It is possible that firstname and
                                    // lastname come separately. So, we
                                    // need to combine them into one.
                                    if (strtolower($key) === "firstname") {
                                        if (isset($name)) {
                                            break;
                                        }
                                        $newContacts[$i][$nameIndex]['value'] = "{$val} {$newContacts[$i][$nameIndex]}";
                                        $nameIndex = -1;
                                    } elseif (strtolower($key) === "lastname") {
                                        if (isset($name)) {
                                            break;
                                        }
                                        $newContacts[$i][$nameIndex]['value'] .= " {$val}";
                                        $nameIndex = -1;
                                    } elseif (strtolower($key) === "name") {
                                        $newContacts[$i][$nameIndex]['value'] = "{$val}";
                                    } else {
                                        $newContacts[$i][] = array(
                                            "id" => "{$field->id}",
                                            "value" => "{$val}",
                                        );
                                    }
                                } else {
                                    $newContacts[$i][] = array(
                                        "id" => "{$field->id}",
                                        "value" => "{$val}",
                                    );

                                    if (strtolower($key) === "name") {
                                        $nameIndex = count($newContacts[$i]) - 1;
                                        $name = $val;
                                    } elseif (strtolower($key) === "firstname") {
                                        $nameIndex = count($newContacts[$i]) - 1;
                                        $firstname = $val;
                                    } elseif (strtolower($key) === "lastname") {
                                        $nameIndex = count($newContacts[$i]) - 1;
                                        $lastname = $val;
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $newContacts;
    }

    /**
     * One way synchronize from platform to SimplyCast CRM.
     *
     * @param string $platform  Supported platform name.
     *
     * @return array Information about the batch that was submitted.
     */
    public function syncFrom($platform)
    {
        // Check whether the platform is supported.
        $this->isSupported($platform, true);

        $plfmHandler = $this->getPlfmHandler($platform);
        if (!isset($plfmHandler)) {
            throw new \Exception(__FUNCTION__ . ': unable to get platform handler.');
        }

        // Get all contacts from the platform
        $contacts = $plfmHandler->getContacts();
        $normContacts = $this->normalize($contacts);
        // Get the corresponding list
        $list = $this->scHandler->contactManager->getListsByName($platform);
        if ($list === null) {
            // List does not exist, create a new one.
            $list = $this->scHandler->contactManager->createList($platform);
            $listId = $list['list']['id'];
        } else {
            $listId = $list['lists'][0]['id'];
        }

        // FIXME: merge column still doesn't work.
        // Use email field as merge column, thus: 23.
        // $response = $this->scHandler->contactManager->createContactBatch($normContacts, 23, intval($listId));
        $response = $this->scHandler->contactManager->createContactBatch(
            $normContacts,
            null,
            intval($listId)
        );
        return $response;
    }
}
