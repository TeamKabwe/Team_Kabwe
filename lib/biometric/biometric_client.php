<?php
require_once(__DIR__ . '/../../config/esignet_config.php');
require_once(__DIR__ . '/../utils/logger.php');

/**
 * Biometric Client
 * This class handles all interactions with biometric devices
 */
class BiometricClient {
    
    /**
     * Initialize the biometric device
     * 
     * @return array Initialization result
     */
    public static function initializeDevice() {
        Logger::debug("Initializing biometric device");
        
        if (!BIOMETRIC_ENABLED) {
            Logger::info("Biometric functionality is disabled");
            return [
                'success' => false,
                'error' => 'Biometric functionality is disabled'
            ];
        }
        
        // In a real implementation, this would communicate with the biometric device
        // For now, we'll simulate a successful initialization
        
        $deviceInfo = [
            'deviceId' => BIOMETRIC_DEVICE_ID,
            'deviceStatus' => 'Ready',
            'certification' => BIOMETRIC_CERTIFICATION,
            'serviceVersion' => BIOMETRIC_SERVICE_VERSION,
            'deviceSubId' => ['0'],
            'callbackId' => '',
            'digitalId' => '',
            'deviceCode' => BIOMETRIC_DEVICE_CODE,
            'specVersion' => [BIOMETRIC_SERVICE_VERSION],
            'purpose' => '',
            'error' => null
        ];
        
        Logger::debug("Biometric device initialized successfully");
        return [
            'success' => true,
            'data' => $deviceInfo
        ];
    }
    
    /**
     * Capture biometric data
     * 
     * @param string $modality Biometric modality (finger, iris, face)
     * @param int $timeout Capture timeout in seconds
     * @return array Captured biometric data or error
     */
    public static function captureBiometric($modality = 'finger', $timeout = 30) {
        Logger::debug("Capturing biometric data. Modality: " . $modality);
        
        if (!BIOMETRIC_ENABLED) {
            Logger::info("Biometric functionality is disabled");
            return [
                'success' => false,
                'error' => 'Biometric functionality is disabled'
            ];
        }
        
        // In a real implementation, this would communicate with the biometric device
        // For now, we'll simulate a successful capture
        
        // Generate a mock biometric data
        $captureTime = date('Y-m-d\TH:i:s.vP');
        $transactionId = 'txn-' . bin2hex(random_bytes(8));
        $biometricData = base64_encode('MOCK_BIOMETRIC_DATA_' . $modality . '_' . time());
        
        $captureResponse = [
            'biometrics' => [
                [
                    'specVersion' => BIOMETRIC_SERVICE_VERSION,
                    'data' => [
                        'digitalId' => self::generateMockDigitalId(),
                        'deviceCode' => BIOMETRIC_DEVICE_CODE,
                        'deviceServiceVersion' => BIOMETRIC_SERVICE_VERSION,
                        'bioType' => $modality,
                        'bioSubType' => $modality === 'finger' ? 'Right Thumb' : '',
                        'purpose' => 'Auth',
                        'env' => 'Staging',
                        'domainUri' => $_SERVER['HTTP_HOST'],
                        'bioValue' => $biometricData,
                        'transactionId' => $transactionId,
                        'timestamp' => $captureTime,
                        'requestedScore' => '80',
                        'qualityScore' => '90'
                    ],
                    'hash' => hash('sha256', $biometricData),
                    'error' => null
                ]
            ]
        ];
        
        Logger::debug("Biometric data captured successfully");
        return [
            'success' => true,
            'data' => $captureResponse
        ];
    }
    
    /**
     * Verify biometric data against stored template
     * 
     * @param array $capturedData Captured biometric data
     * @param string $userId User ID for verification
     * @return array Verification result
     */
    public static function verifyBiometric($capturedData, $userId) {
        Logger::debug("Verifying biometric data for user: " . $userId);
        
        if (!BIOMETRIC_ENABLED) {
            Logger::info("Biometric functionality is disabled");
            return [
                'success' => false,
                'error' => 'Biometric functionality is disabled'
            ];
        }
        
        // In a real implementation, this would send the biometric data to eSignet for verification
        // For now, we'll simulate a successful verification
        
        // Simulate API call delay
        usleep(1000000); // 1 second
        
        $verificationResult = [
            'verified' => true,
            'score' => 95.5,
            'timestamp' => date('Y-m-d\TH:i:s.vP'),
            'transactionId' => 'ver-' . bin2hex(random_bytes(8))
        ];
        
        Logger::debug("Biometric verification successful");
        return [
            'success' => true,
            'data' => $verificationResult
        ];
    }
    
    /**
     * Generate a mock digital ID for the biometric device
     * 
     * @return string JSON string of digital ID
     */
    private static function generateMockDigitalId() {
        $digitalId = [
            'serialNo' => 'BSDT' . rand(10000, 99999),
            'make' => 'MOSIP',
            'model' => 'SLAP01',
            'type' => 'Finger',
            'deviceSubType' => 'Slap',
            'deviceProvider' => 'MOSIP',
            'deviceProviderId' => 'MOSIP.PROXY.SBI',
            'dateTime' => date('Y-m-d\TH:i:s.vP')
        ];
        
        return json_encode($digitalId);
    }
}
?>

