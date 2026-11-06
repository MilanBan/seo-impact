<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

<user-scope-rules>
# Scope Control

- Treat requests about style, visual design, layout, colors, contrast, typography, spacing, or animation as visual-only work.
- Preserve all existing functionality while applying visual changes. Do not add, remove, enable, disable, or alter features, authentication methods, routes, controllers, models, business logic, stored data, or dependencies unless the user explicitly requests that functional change.
- A request to make one screen look like another authorizes visual alignment only; it does not authorize removing or changing controls or their behavior.
- If a visual request appears to require a functional change, explain the proposed functional impact and ask for explicit approval before implementing it.
- When the requested scope is ambiguous, ask the user before making any functional or behavioral change.
  </user-scope-rules>
