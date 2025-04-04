"use client"

import { useState, useEffect } from "react"
import { Bell, Menu } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet"
import { Sidebar } from "@/components/sidebar"
import { LanguageSelector } from "@/components/language-selector"
import { Badge } from "@/components/ui/badge"
import { useTranslation } from "@/lib/i18n/client"

interface HeaderProps {
  title?: string
}

export function Header({ title }: HeaderProps) {
  const { t } = useTranslation()
  const [user, setUser] = useState<any>(null)
  const [notifications, setNotifications] = useState<number>(0)

  useEffect(() => {
    // Get user from localStorage
    const userData = localStorage.getItem("user")
    if (userData) {
      setUser(JSON.parse(userData))
    }

    // Mock notifications
    setNotifications(3)
  }, [])

  return (
    <header className="sticky top-0 z-10 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="flex h-14 items-center px-4">
        <Sheet>
          <SheetTrigger asChild>
            <Button variant="outline" size="icon" className="md:hidden">
              <Menu className="h-5 w-5" />
              <span className="sr-only">Toggle menu</span>
            </Button>
          </SheetTrigger>
          <SheetContent side="left" className="p-0">
            <Sidebar />
          </SheetContent>
        </Sheet>

        <div className="ml-4 md:ml-0 font-semibold">{title || t("header.defaultTitle")}</div>

        <div className="ml-auto flex items-center gap-2">
          <LanguageSelector />

          <Button variant="ghost" size="icon" className="relative">
            <Bell className="h-5 w-5" />
            {notifications > 0 && (
              <Badge
                variant="destructive"
                className="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0 text-xs"
              >
                {notifications}
              </Badge>
            )}
          </Button>

          <div className="flex items-center gap-2 ml-2">
            <div className="hidden md:block text-right">
              {user && (
                <>
                  <p className="text-sm font-medium">{user.name}</p>
                  <p className="text-xs text-muted-foreground">{user.farmerId}</p>
                </>
              )}
            </div>
            <img src="/placeholder.svg?height=32&width=32" alt="User avatar" className="h-8 w-8 rounded-full" />
          </div>
        </div>
      </div>
    </header>
  )
}

