"use client"

import { useEffect, useState } from "react"
import { useRouter, useSearchParams } from "next/navigation"
import { handleEsignetCallback } from "@/lib/esignet/esignet"
import { Card, CardContent } from "@/components/ui/card"
import { Loader2 } from "lucide-react"

export default function EsignetCallbackPage() {
  const router = useRouter()
  const searchParams = useSearchParams()
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    const processCallback = async () => {
      try {
        const code = searchParams.get("code")
        if (!code) {
          throw new Error("No authorization code received")
        }

        // Process the callback with the code
        const userData = await handleEsignetCallback(code)

        // Store user data and token
        localStorage.setItem("isAuthenticated", "true")
        localStorage.setItem("user", JSON.stringify(userData))
        localStorage.setItem("authMethod", "esignet")

        // Redirect to dashboard
        router.push("/dashboard")
      } catch (err) {
        console.error("Error processing eSignet callback:", err)
        setError(err instanceof Error ? err.message : "Authentication failed")
      }
    }

    processCallback()
  }, [router, searchParams])

  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center p-4">
        <Card className="w-full max-w-md">
          <CardContent className="pt-6">
            <div className="text-center space-y-4">
              <h2 className="text-xl font-semibold text-destructive">Authentication Error</h2>
              <p>{error}</p>
              <button onClick={() => router.push("/auth/login")} className="text-primary hover:underline">
                Return to login
              </button>
            </div>
          </CardContent>
        </Card>
      </div>
    )
  }

  return (
    <div className="min-h-screen flex items-center justify-center">
      <div className="text-center space-y-4">
        <Loader2 className="h-8 w-8 animate-spin mx-auto" />
        <p>Processing your login...</p>
      </div>
    </div>
  )
}

