<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AddOnManager;
use Illuminate\Support\Facades\Log;

class AddOnController extends Controller
{
    protected $addOnManager;
    
    public function __construct(AddOnManager $addOnManager)
    {
        $this->addOnManager = $addOnManager;
    }
    
    /**
     * Display add-on management interface
     */
    public function index()
    {
        $addons = $this->addOnManager->getAvailableAddOns();
        
        return view('addons.index', compact('addons'));
    }
    
    /**
     * Install an add-on
     */
    public function install(Request $request, $addonName)
    {
        try {
            $this->addOnManager->installAddOn($addonName);
            
            return response()->json([
                'success' => true,
                'message' => "Add-on {$addonName} installed successfully."
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Uninstall an add-on
     */
    public function uninstall(Request $request, $addonName)
    {
        try {
            $this->addOnManager->uninstallAddOn($addonName);
            
            return response()->json([
                'success' => true,
                'message' => "Add-on {$addonName} uninstalled successfully."
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Enable an add-on
     */
    public function enable(Request $request, $addonName)
    {
        try {
            $result = $this->addOnManager->enableAddOn($addonName);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => "Add-on {$addonName} enabled successfully."
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Add-on {$addonName} not found or not installed."
                ], 404);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Disable an add-on
     */
    public function disable(Request $request, $addonName)
    {
        try {
            $result = $this->addOnManager->disableAddOn($addonName);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => "Add-on {$addonName} disabled successfully."
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Add-on {$addonName} not found or not installed."
                ], 404);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get add-on details
     */
    public function show($addonName)
    {
        $addons = $this->addOnManager->getAvailableAddOns();
        
        if (!isset($addons[$addonName])) {
            return response()->json([
                'success' => false,
                'message' => "Add-on {$addonName} not found."
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'addon' => $addons[$addonName]
        ]);
    }
}
