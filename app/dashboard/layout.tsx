"use client"

import type React from "react"

import { useEffect } from "react"
import { useRouter } from "next/navigation"
import { useAuth } from "@/lib/auth-context"
import Link from "next/link"

export default function DashboardLayout({
  children,
}: {
  children: React.ReactNode
}) {
  const { user, isLoading, logout } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (!isLoading && !user) {
      router.push("/login")
    }
  }, [isLoading, user, router])

  if (isLoading) {
    return (
      <div className="flex h-screen items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    )
  }

  if (!user) {
    return null
  }

  return (
    <div className="flex h-screen bg-gray-100">
      {/* Sidebar */}
      <div className="w-64 bg-white shadow-md">
        <div className="p-4 border-b">
          <h2 className="font-bold text-lg">SADC Farmer ID</h2>
          <p className="text-sm text-gray-600">{user.name}</p>
          <p className="text-xs text-gray-500">{user.farmerId}</p>
        </div>

        <nav className="p-4">
          <ul className="space-y-2">
            <li>
              <Link href="/dashboard" className="block px-4 py-2 rounded hover:bg-gray-100">
                🏠 Dashboard
              </Link>
            </li>
            <li>
              <Link href="/dashboard/loans" className="block px-4 py-2 rounded hover:bg-gray-100">
                💰 Apply for Loan
              </Link>
            </li>
            <li>
              <Link href="/dashboard/fertilizers" className="block px-4 py-2 rounded hover:bg-gray-100">
                🌾 Fertilizer Access
              </Link>
            </li>
            <li>
              <Link href="/dashboard/markets" className="block px-4 py-2 rounded hover:bg-gray-100">
                🛒 Market Prices
              </Link>
            </li>
            <li>
              <Link href="/dashboard/profile" className="block px-4 py-2 rounded hover:bg-gray-100">
                👤 Farmer Profile
              </Link>
            </li>
            <li>
              <Link href="/dashboard/settings" className="block px-4 py-2 rounded hover:bg-gray-100">
                ⚙️ Settings
              </Link>
            </li>
            <li>
              <button
                onClick={logout}
                className="w-full text-left block px-4 py-2 rounded hover:bg-gray-100 text-red-600"
              >
                🚪 Logout
              </button>
            </li>
          </ul>
        </nav>
      </div>

      {/* Main content */}
      <div className="flex-1 overflow-auto">
        <header className="bg-white shadow-sm p-4 flex justify-between items-center">
          <h1 className="text-xl font-semibold">SADC Digital Farmer ID</h1>
          <div className="flex items-center gap-2">
            <span className="text-sm">{user.name}</span>
            <div className="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
              {user.name.charAt(0)}
            </div>
          </div>
        </header>

        <main className="p-6">{children}</main>
      </div>
    </div>
  )
}

