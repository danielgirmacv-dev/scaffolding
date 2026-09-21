<?php

namespace App\Services;

use App\Models\Site;

class SiteContext
{
    public const SESSION_KEY = 'active_site_context_id';

    public static function getActiveSiteId(): ?int
    {
        $siteId = session(self::SESSION_KEY);

        if ($siteId && Site::where('id', $siteId)->exists()) {
            return (int) $siteId;
        }

        // Default to first active site if available
        $defaultSite = Site::where('is_central_store', true)->first()
            ?? Site::where('status', 'active')->first()
            ?? Site::first();

        if ($defaultSite) {
            session([self::SESSION_KEY => $defaultSite->id]);

            return $defaultSite->id;
        }

        return null;
    }

    public static function setActiveSiteId(?int $siteId): void
    {
        if ($siteId === null) {
            session()->forget(self::SESSION_KEY);
        } else {
            session([self::SESSION_KEY => $siteId]);
        }
    }

    public static function getActiveSite(): ?Site
    {
        $siteId = self::getActiveSiteId();

        return $siteId ? Site::find($siteId) : null;
    }

    public static function getActiveSiteName(): string
    {
        $site = self::getActiveSite();

        return $site ? "[{$site->code}] {$site->name}" : 'Company Balance (Global Context)';
    }

    public static function isGlobalContext(): bool
    {
        return session(self::SESSION_KEY) === null || session(self::SESSION_KEY) === 'global';
    }

    public static function setGlobalContext(): void
    {
        session([self::SESSION_KEY => 'global']);
    }

    public static function confirmationMessage(string $siteName): string
    {
        return "Now viewing {$siteName}. All balances and operations will apply to this site.";
    }
}
