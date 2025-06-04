<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/ai-assistant', name: 'app_ai_assistant', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function aiAssistant(Request $request): JsonResponse
    {
        $message = $request->get('message', '');
        $user = $this->getUser();
        
        if (empty($message)) {
            return $this->json(['error' => 'Message is required'], 400);
        }
        
        // AI Assistant responses based on user input
        $responses = $this->getAIResponses();
        $lowerMessage = strtolower($message);
        
        // Find the most appropriate response
        $response = $this->findBestResponse($lowerMessage, $responses);
        
        // Personalize the response if possible
        $personalizedResponse = $this->personalizeResponse($response, $user);
        
        return $this->json([
            'message' => $personalizedResponse,
            'timestamp' => date('c'),
            'user' => $user->getEmail()
        ]);
    }
    
    private function getAIResponses(): array
    {
        return [
            'help' => 'I can help you with document management, security, sharing, and account settings. What specific area would you like assistance with?',
            'upload' => 'To upload documents: Go to "My Documents" → Click "Upload Document" → Select your file → Add title and description → Click "Upload". Supported formats: PDF, DOC, DOCX, TXT, and images (max 10MB).',
            'share' => 'To share a document: Go to your document list → Click the share icon → Set access limits (time/usage) → Copy the generated link. You can revoke access anytime from document details.',
            'security' => 'Your documents are protected with enterprise-grade security: encryption at rest and in transit, user authentication, access controls, and secure sharing tokens. Only you can access your documents unless explicitly shared.',
            'organize' => 'Document organization tips: Use descriptive titles, add detailed descriptions, utilize the search feature, and regularly review old documents. Consider categorizing by project or date.',
            'account' => 'Manage your account by clicking your profile picture. You can: edit profile information, change password, update profile photo, and adjust account settings.',
            'search' => 'Use the search bar on your documents page to find files by title. The search works in real-time as you type and provides instant results.',
            'profile' => 'Update your profile: Profile picture → Profile → Edit Profile. Change name, email, and photo. Use "Change Password" for security updates.',
            'password' => 'To change your password: Click your profile → Profile → Change Password. Enter current password and new password twice for confirmation.',
            'photo' => 'Update profile photo: Go to Profile → Edit Profile → Choose new photo → Save. Supported formats: JPG, PNG, GIF (max 2MB).',
            'limit' => 'File upload limits: Maximum 10MB per file. Supported formats: PDF, DOC, DOCX, TXT, JPG, PNG, GIF. Contact support if you need larger file support.',
            'delete' => 'To delete documents: Go to document details → Click "Delete Document" → Confirm action. Warning: This action cannot be undone.',
            'download' => 'Download documents: Click the document title or use the download button in document details. Files are downloaded with their original names.',
            'privacy' => 'Privacy: Your documents are private by default. Only you can access them unless you create a share link. We never access your document content.',
            'support' => 'Need more help? Check our documentation, contact support, or ask me about specific features. I\'m here to help with any questions!',
            'default' => 'I can help you with document management, security, sharing, and account settings. Try asking about: uploading, sharing, security, organizing, or account management.'
        ];
    }
    
    private function findBestResponse(string $message, array $responses): string
    {
        // Check for exact keyword matches
        foreach ($responses as $keyword => $response) {
            if ($keyword !== 'default' && strpos($message, $keyword) !== false) {
                return $response;
            }
        }
        
        // Check for related terms
        $synonyms = [
            'upload' => ['add', 'upload', 'file', 'document', 'submit'],
            'share' => ['share', 'send', 'collaborate', 'access', 'link'],
            'security' => ['secure', 'safe', 'protection', 'privacy', 'encrypt'],
            'organize' => ['organize', 'manage', 'sort', 'structure', 'arrange'],
            'search' => ['find', 'search', 'look', 'locate', 'discover'],
            'delete' => ['delete', 'remove', 'erase', 'trash'],
            'download' => ['download', 'get', 'retrieve', 'save'],
            'password' => ['password', 'login', 'credential', 'auth'],
            'profile' => ['profile', 'account', 'settings', 'info']
        ];
        
        foreach ($synonyms as $keyword => $terms) {
            foreach ($terms as $term) {
                if (strpos($message, $term) !== false) {
                    return $responses[$keyword] ?? $responses['default'];
                }
            }
        }
        
        return $responses['default'];
    }
    
    private function personalizeResponse(string $response, $user): string
    {
        $name = $user->getFullName() ?: $user->getEmail();
        
        // Add personal greeting for certain responses
        if (strpos($response, 'I can help you') === 0) {
            return "Hello {$name}! " . $response;
        }
        
        return $response;
    }
}
