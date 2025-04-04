"use client"

import { useState, useEffect } from "react"
import Link from "next/link"
import { usePathname } from "next/navigation"
import { useRouter } from "next/navigation"
import { MdDashboard, MdLogout } from "react-icons/md"
import { FaMoneyCheck, FaShoppingBasket, FaUserCircle } from "react-icons/fa"
import { GiGrain } from "react-icons/gi"
import { FiSettings } from "react-icons/fi"
import { Button } from "@/components/ui/button"
import { useTranslation } from "@/lib/i18n/client"
import { cn } from "@/lib/utils"

interface SidebarProps {
  className?: string
}

export function Sidebar({ className }: SidebarProps) {
  const pathname = usePathname()
  const router = useRouter()
  const { t } = useTranslation()
  const [user, setUser] = useState<any>(null)

  useEffect(() => {
    // Get user from localStorage
    const userData = localStorage.getItem("user")
    if (userData) {
      setUser(JSON.parse(userData))
    }
  }, [])

  const handleLogout = () => {
    localStorage.removeItem("isAuthenticated")
    localStorage.removeItem("user")
    localStorage.removeItem("authMethod")
    router.push("/auth/login")
  }

  const navItems = [
    {
      name: t("sidebar.dashboard"),
      href: "/dashboard",
      icon: MdDashboard,
    },
    {
      name: t("sidebar.loans"),
      href: "/loans",
      icon: FaMoneyCheck,
    },
    {
      name: t("sidebar.fertilizers"),
      href: "/fertilizers",
      icon: GiGrain,
    },
    {
      name: t("sidebar.markets"),
      href: "/markets",
      icon: FaShoppingBasket,
    },
    {
      name: t("sidebar.profile"),
      href: "/profile",
      icon: FaUserCircle,
    },
    {
      name: t("sidebar.settings"),
      href: "/settings",
      icon: FiSettings,
    },
  ]

  return (
    <div className={cn("flex flex-col h-full bg-card border-r", className)}>
      <div className="p-4 border-b">
        <div className="flex items-center gap-2">
          <img src="/placeholder.svg?height=40&width=40" alt="SADC Logo" className="h-10 w-10" />
          <div>
            <h2 className="font-bold text-lg">SADC Farmer ID</h2>
            {user && <p className="text-sm text-muted-foreground truncate max-w-[150px]">{user.name}</p>}
          </div>
        </div>
      </div>

      <nav className="flex-1 p-4">
        <ul className="space-y-2">
          {navItems.map((item) => (
            <li key={item.href}>
              <Link
                href={item.href}
                className={cn(
                  "flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors",
                  pathname === item.href ? "bg-primary text-primary-foreground" : "hover:bg-muted",
                )}
              >
                <item.icon className="h-5 w-5" />
                {item.name}
              </Link>
            </li>
          ))}
        </ul>
      </nav>

      <div className="p-4 border-t mt-auto">
        <Button
          variant="ghost"
          className="w-full justify-start text-destructive hover:text-destructive hover:bg-destructive/10"
          onClick={handleLogout}
        >
          <MdLogout className="mr-2 h-5 w-5" />
          {t("sidebar.logout")}
        </Button>
      </div>
    </div>
  )
}

