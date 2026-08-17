<?php
require_once '../includes/auth.php'; // ensure session is started
require_once '../includes/db.php';
require_once '../vendor/autoload.php';

use lbuchs\WebAuthn\WebAuthn;

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Format used for relying party
$rpName = 'Program Alert App';
$rpId = $_SERVER['HTTP_HOST'] ?? 'localhost'; 

// Prepare WebAuthn
$webauthn = new WebAuthn($rpName, $rpId, ['apple', 'android-key', 'android-safetynet', 'fido-u2f', 'none', 'packed', 'tpm']);

try {
    if ($action === 'get_registration_challenge') {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Must be logged in to register a device.");
        }
        
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $username = $stmt->fetchColumn();
        
        // Generate challenge
        $createArgs = $webauthn->getCreateArgs((string)$userId, $username, $username, 20, true, 'preferred');
        
        $_SESSION['webauthn_challenge'] = $webauthn->getChallenge();
        
        echo json_encode($createArgs);
        exit;
    }
    
    if ($action === 'verify_registration') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['webauthn_challenge'])) {
            throw new Exception("Session error.");
        }
        
        $clientDataJSON = base64_decode($_POST['clientDataJSON'] ?? '');
        $attestationObject = base64_decode($_POST['attestationObject'] ?? '');
        
        $data = $webauthn->processCreate($clientDataJSON, $attestationObject, $_SESSION['webauthn_challenge'], true, true, false);
        
        $credentialId = base64_encode($data->credentialId);
        $credentialPublicKey = $data->credentialPublicKey;
        
        // Store in DB
        $stmt = $pdo->prepare("INSERT INTO webauthn_credentials (user_id, credential_id, public_key) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $credentialId, $credentialPublicKey]);
        
        echo json_encode(['status' => 'success', 'msg' => 'Device registered successfully.']);
        exit;
    }

    if ($action === 'get_login_challenge') {
        // We will do a discoverable credential (passwordless) if possible, 
        // or just a standard get args if username is provided.
        // For simplicity, we get all credential IDs for the username if provided, or allow all.
        $username = $_POST['username'] ?? '';
        
        $credentialIds = [];
        if ($username) {
            $stmt = $pdo->prepare("SELECT wc.credential_id FROM webauthn_credentials wc JOIN users u ON wc.user_id = u.id WHERE u.username = ?");
            $stmt->execute([$username]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $credentialIds[] = base64_decode($row['credential_id']);
            }
            if (empty($credentialIds)) {
                throw new Exception("No WebAuthn credentials found for this user.");
            }
        }
        
        $getArgs = $webauthn->getGetArgs($credentialIds, 20, true, true, true, true, true, 'preferred');
        
        $_SESSION['webauthn_challenge'] = $webauthn->getChallenge();
        
        echo json_encode($getArgs);
        exit;
    }
    
    if ($action === 'verify_login') {
        if (!isset($_SESSION['webauthn_challenge'])) {
            throw new Exception("Session error or challenge missing.");
        }
        
        $clientDataJSON = base64_decode($_POST['clientDataJSON'] ?? '');
        $authenticatorData = base64_decode($_POST['authenticatorData'] ?? '');
        $signature = base64_decode($_POST['signature'] ?? '');
        $id = $_POST['id'] ?? '';
        $userHandle = base64_decode($_POST['userHandle'] ?? '');
        
        // Find public key by credential ID
        $stmt = $pdo->prepare("SELECT u.id as user_id, u.role, wc.public_key FROM webauthn_credentials wc JOIN users u ON wc.user_id = u.id WHERE wc.credential_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            throw new Exception("Credential not found.");
        }
        
        $credentialPublicKey = $row['public_key'];
        
        // Verify
        $webauthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $_SESSION['webauthn_challenge'], null, true, true);
        
        // Login success
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role'];
        
        echo json_encode(['status' => 'success', 'msg' => 'Logged in successfully.']);
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
    exit;
}
