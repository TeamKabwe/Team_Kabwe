"use client"

import { createContext, useContext, useState, useEffect, type ReactNode } from "react"
import { useRouter } from "next/navigation"

// Initial mock users
const initialMockUsers = [
  {
    id: "1",
    name: "John Doe",
    email: "farmer@example.com",
    password: "password123",
    farmerId: "SADC-123456",
  },
  {
    id: "2",
    name: "Maria Moyo",
    email: "maria@example.com",
    password: "password123",
    farmerId: "SADC-789012",
  },
]

type User = {
  id: string
  name: string
  email: string
  farmerId: string
}

type AuthContextType = {
  user: User | null
  isLoading: boolean
  login: (email: string, password: string) => Promise<boolean>
  loginWithEsignet: () => void
  logout: () => void
  register: (name: string, email: string, password: string) => Promise<boolean>
  users: Array<{ id: string; name: string; email: string; password: string; farmerId: string }>
}

const AuthContext = createContext<AuthContextType | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const [users, setUsers] = useState(() => {
    // Try to get users from localStorage first
    if (typeof window !== "undefined") {
      const storedUsers = localStorage.getItem("mockUsers")
      return storedUsers ? JSON.parse(storedUsers) : initialMockUsers
    }
    return initialMockUsers
  })

  const router = useRouter()

  useEffect(() => {
    // Check if user is logged in
    const storedUser = localStorage.getItem("user")
    if (storedUser) {
      setUser(JSON.parse(storedUser))
    }

    // Store users in localStorage if not already there
    if (typeof window !== "undefined" && !localStorage.getItem("mockUsers")) {
      localStorage.setItem("mockUsers", JSON.stringify(users))
    }

    setIsLoading(false)
  }, [users])

  const login = async (email: string, password: string): Promise<boolean> => {
    setIsLoading(true)

    // Simulate API call
    await new Promise((resolve) => setTimeout(resolve, 500))

    // Get latest users from localStorage
    const storedUsers = localStorage.getItem("mockUsers")
    const currentUsers = storedUsers ? JSON.parse(storedUsers) : users

    const foundUser = currentUsers.find((u: any) => u.email === email && u.password === password)

    if (foundUser) {
      const { password: _, ...userWithoutPassword } = foundUser
      setUser(userWithoutPassword)
      localStorage.setItem("user", JSON.stringify(userWithoutPassword))
      localStorage.setItem("isAuthenticated", "true")
      setIsLoading(false)
      return true
    }

    setIsLoading(false)
    return false
  }

  const loginWithEsignet = () => {
    // Simulate eSignet login
    setIsLoading(true)

    // In a real app, this would redirect to eSignet
    // For demo, we'll just simulate a successful login after a delay
    setTimeout(() => {
      const esignetUser = {
        id: "2",
        name: "Maria Moyo",
        email: "maria@example.com",
        farmerId: "SADC-789012",
      }

      setUser(esignetUser)
      localStorage.setItem("user", JSON.stringify(esignetUser))
      localStorage.setItem("isAuthenticated", "true")
      localStorage.setItem("authMethod", "esignet")
      setIsLoading(false)

      router.push("/dashboard")
    }, 1000)
  }

  const register = async (name: string, email: string, password: string): Promise<boolean> => {
    setIsLoading(true)

    // Simulate API call
    await new Promise((resolve) => setTimeout(resolve, 500))

    // Get latest users from localStorage
    const storedUsers = localStorage.getItem("mockUsers")
    const currentUsers = storedUsers ? JSON.parse(storedUsers) : users

    // Check if email already exists
    if (currentUsers.some((u: any) => u.email === email)) {
      setIsLoading(false)
      return false
    }

    // Create new user
    const newUser = {
      id: (currentUsers.length + 1).toString(),
      name,
      email,
      password,
      farmerId: `SADC-${Math.floor(100000 + Math.random() * 900000)}`,
    }

    // Add to users array
    const updatedUsers = [...currentUsers, newUser]
    setUsers(updatedUsers)

    // Update localStorage
    localStorage.setItem("mockUsers", JSON.stringify(updatedUsers))

    // Auto login
    const { password: _, ...userWithoutPassword } = newUser
    setUser(userWithoutPassword)
    localStorage.setItem("user", JSON.stringify(userWithoutPassword))
    localStorage.setItem("isAuthenticated", "true")

    setIsLoading(false)
    return true
  }

  const logout = () => {
    setUser(null)
    localStorage.removeItem("user")
    localStorage.removeItem("isAuthenticated")
    localStorage.removeItem("authMethod")
    router.push("/login")
  }

  return (
    <AuthContext.Provider
      value={{
        user,
        isLoading,
        login,
        loginWithEsignet,
        logout,
        register,
        users,
      }}
    >
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error("useAuth must be used within an AuthProvider")
  }
  return context
}

