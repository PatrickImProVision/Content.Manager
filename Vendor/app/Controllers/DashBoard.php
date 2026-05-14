<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\ModuleSettings;
use App\Libraries\WebAnalytics;
use App\Libraries\WebSettings;
use CodeIgniter\HTTP\ResponseInterface;

class DashBoard extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index(): string|ResponseInterface
    {
        $login = $this->requireLogin();
        if ($login instanceof ResponseInterface) {
            return $login;
        }

        $memberCanManageRoles = $this->canManageContentModules();
        $moduleSettings = new ModuleSettings();
        $contentModules = $moduleSettings->contentModules();
        $analyticsEnabled = $moduleSettings->isEnabled(ModuleSettings::WEB_ANALYTICS);

        return view('dashboard/index', [
            'title'            => 'Dashboard',
            'wideLayout'       => true,
            'contentModules'   => $contentModules,
            'analyticsEnabled' => $analyticsEnabled,
            'analytics'        => $memberCanManageRoles && $analyticsEnabled ? (new WebAnalytics())->dashboardSummary() : null,
        ]);
    }

    public function moduleManager(): string|ResponseInterface
    {
        $login = $this->requireLogin();
        if ($login instanceof ResponseInterface) {
            return $login;
        }

        if (! $this->canManageContentModules()) {
            return redirect()->to(site_url('DashBoard/Index'))->with('errors', ['dashboard' => 'Only Administrator, Manager, or Owner accounts can manage modules.']);
        }

        return view('dashboard/module_manager', [
            'title'                   => 'Module Manager',
            'wideLayout'              => true,
            'canManageContentModules' => true,
            'contentModules'          => (new ModuleSettings())->contentModules(),
        ]);
    }

    public function saveContentModules(): ResponseInterface
    {
        $login = $this->requireLogin();
        if ($login instanceof ResponseInterface) {
            return $login;
        }

        if (! $this->canManageContentModules()) {
            return redirect()->to(site_url('DashBoard/Index'))->with('errors', ['dashboard' => 'Only Administrator, Manager, or Owner accounts can manage modules.']);
        }

        $enabled = $this->request->getPost('modules') ?? $this->request->getPost('content_modules');
        $enabled = is_array($enabled) ? array_map('strval', $enabled) : [];

        (new ModuleSettings())->saveContentModules($enabled);

        return redirect()->to(site_url('DashBoard/ModuleManager/Index'))->with('message', 'Module settings updated.');
    }

    public function webSettings(): string|ResponseInterface
    {
        $login = $this->requireLogin();
        if ($login instanceof ResponseInterface) {
            return $login;
        }

        if (! $this->canManageContentModules()) {
            return redirect()->to(site_url('DashBoard/Index'))->with('errors', ['dashboard' => 'Only Administrator, Manager, or Owner accounts can manage web settings.']);
        }

        return view('dashboard/web_settings', [
            'title'          => 'Web Settings',
            'wideLayout'     => true,
            'contentModules' => (new ModuleSettings())->contentModules(),
            'webSettings'    => (new WebSettings())->homeSettings(),
        ]);
    }

    public function saveWebSettings(): ResponseInterface
    {
        $login = $this->requireLogin();
        if ($login instanceof ResponseInterface) {
            return $login;
        }

        if (! $this->canManageContentModules()) {
            return redirect()->to(site_url('DashBoard/Index'))->with('errors', ['dashboard' => 'Only Administrator, Manager, or Owner accounts can manage web settings.']);
        }

        $webName = (string) $this->request->getPost('web_name');
        $webDescription = (string) $this->request->getPost('web_description');

        if (trim($webName) === '' || trim($webDescription) === '') {
            return redirect()->back()->withInput()->with('errors', ['web_settings' => 'Web name and description are required.']);
        }

        (new WebSettings())->saveHomeSettings($webName, $webDescription);

        return redirect()->to(site_url('DashBoard/WebSettings/Index'))->with('message', 'Web settings updated.');
    }

    private function canManageContentModules(): bool
    {
        return is_numeric(session()->get('member_user_id')) && (bool) session()->get('member_can_manage_roles');
    }

    private function requireLogin(): ?ResponseInterface
    {
        if (is_numeric(session()->get('member_user_id'))) {
            return null;
        }

        return redirect()->to(site_url('Member/User/Login'))->with('errors', ['auth' => 'Log in to continue.']);
    }
}
