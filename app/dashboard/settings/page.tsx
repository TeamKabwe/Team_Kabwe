"use client"

import type React from "react"

import { useState } from "react"
import { useAuth } from "@/lib/auth-context"

export default function SettingsPage() {
  const { user, logout } = useAuth()
  const [activeTab, setActiveTab] = useState("account")
  const [isEsignetLinked, setIsEsignetLinked] = useState(localStorage.getItem("authMethod") === "esignet")
  const [isChangingPassword, setIsChangingPassword] = useState(false)
  const [passwordForm, setPasswordForm] = useState({
    currentPassword: "",
    newPassword: "",
    confirmPassword: "",
  })
  const [privacySettings, setPrivacySettings] = useState({
    shareData: true,
    receiveNotifications: true,
    marketUpdates: true,
    locationTracking: false,
  })

  const handlePasswordChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target
    setPasswordForm((prev) => ({
      ...prev,
      [name]: value,
    }))
  }

  const handleUpdatePassword = (e: React.FormEvent) => {
    e.preventDefault()
    setIsChangingPassword(true)

    // Validate passwords
    if (passwordForm.newPassword !== passwordForm.confirmPassword) {
      alert("New passwords do not match")
      setIsChangingPassword(false)
      return
    }

    // Simulate API call
    setTimeout(() => {
      setIsChangingPassword(false)
      alert("Password updated successfully")

      // Reset form
      setPasswordForm({
        currentPassword: "",
        newPassword: "",
        confirmPassword: "",
      })
    }, 1000)
  }

  const handleToggleEsignet = () => {
    if (isEsignetLinked) {
      // Unlink eSignet
      localStorage.removeItem("authMethod")
      setIsEsignetLinked(false)
      alert("eSignet account unlinked successfully")
    } else {
      // Link eSignet - in a real app, this would redirect to eSignet
      alert("This would redirect to eSignet for authentication")
      // Simulate successful linking
      setTimeout(() => {
        localStorage.setItem("authMethod", "esignet")
        setIsEsignetLinked(true)
        alert("eSignet account linked successfully")
      }, 1000)
    }
  }

  const handlePrivacyToggle = (setting: keyof typeof privacySettings) => {
    setPrivacySettings((prev) => ({
      ...prev,
      [setting]: !prev[setting],
    }))
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Settings</h1>

      <div className="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div className="border-b border-gray-200">
          <nav className="-mb-px flex">
            <button
              onClick={() => setActiveTab("account")}
              className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                activeTab === "account"
                  ? "border-blue-500 text-blue-600"
                  : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
              }`}
            >
              Account
            </button>
            <button
              onClick={() => setActiveTab("esignet")}
              className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                activeTab === "esignet"
                  ? "border-blue-500 text-blue-600"
                  : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
              }`}
            >
              eSignet
            </button>
            <button
              onClick={() => setActiveTab("privacy")}
              className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                activeTab === "privacy"
                  ? "border-blue-500 text-blue-600"
                  : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
              }`}
            >
              Privacy
            </button>
          </nav>
        </div>

        <div className="p-6">
          {activeTab === "account" && (
            <form onSubmit={handleUpdatePassword} className="space-y-4">
              <h2 className="text-lg font-semibold mb-4">Change Password</h2>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input
                  type="password"
                  name="currentPassword"
                  value={passwordForm.currentPassword}
                  onChange={handlePasswordChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input
                  type="password"
                  name="newPassword"
                  value={passwordForm.newPassword}
                  onChange={handlePasswordChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input
                  type="password"
                  name="confirmPassword"
                  value={passwordForm.confirmPassword}
                  onChange={handlePasswordChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md"
                  required
                />
              </div>

              <button
                type="submit"
                disabled={isChangingPassword}
                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                {isChangingPassword ? "Updating..." : "Update Password"}
              </button>
            </form>
          )}

          {activeTab === "esignet" && (
            <div className="space-y-4">
              <h2 className="text-lg font-semibold mb-4">eSignet Settings</h2>

              <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h3 className="font-medium">eSignet Account</h3>
                  <p className="text-sm text-gray-500">
                    {isEsignetLinked
                      ? "Your account is linked with eSignet"
                      : "Link your account with eSignet for secure authentication"}
                  </p>
                </div>
                <button
                  onClick={handleToggleEsignet}
                  className={`px-4 py-2 rounded-md ${
                    isEsignetLinked
                      ? "bg-red-600 text-white hover:bg-red-700"
                      : "bg-blue-600 text-white hover:bg-blue-700"
                  }`}
                >
                  {isEsignetLinked ? "Unlink eSignet" : "Link eSignet"}
                </button>
              </div>

              {isEsignetLinked && (
                <div className="bg-gray-100 p-4 rounded-md">
                  <h4 className="font-medium">eSignet Information</h4>
                  <div className="mt-2 text-sm">
                    <div className="flex justify-between py-1">
                      <span className="text-gray-500">Status:</span>
                      <span>Connected</span>
                    </div>
                    <div className="flex justify-between py-1">
                      <span className="text-gray-500">Last Used:</span>
                      <span>Today</span>
                    </div>
                    <div className="flex justify-between py-1">
                      <span className="text-gray-500">Permissions:</span>
                      <span>Identity, Basic Profile</span>
                    </div>
                  </div>
                </div>
              )}
            </div>
          )}

          {activeTab === "privacy" && (
            <div className="space-y-6">
              <h2 className="text-lg font-semibold mb-4">Privacy Settings</h2>

              <div className="flex items-center justify-between">
                <div>
                  <h3 className="font-medium">Data Sharing</h3>
                  <p className="text-sm text-gray-500">Share your farming data with agricultural services</p>
                </div>
                <label className="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    checked={privacySettings.shareData}
                    onChange={() => handlePrivacyToggle("shareData")}
                    className="sr-only peer"
                  />
                  <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>

              <div className="flex items-center justify-between">
                <div>
                  <h3 className="font-medium">Notifications</h3>
                  <p className="text-sm text-gray-500">Receive notifications about your account and services</p>
                </div>
                <label className="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    checked={privacySettings.receiveNotifications}
                    onChange={() => handlePrivacyToggle("receiveNotifications")}
                    className="sr-only peer"
                  />
                  <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>

              <div className="flex items-center justify-between">
                <div>
                  <h3 className="font-medium">Market Updates</h3>
                  <p className="text-sm text-gray-500">Receive updates about market prices and trends</p>
                </div>
                <label className="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    checked={privacySettings.marketUpdates}
                    onChange={() => handlePrivacyToggle("marketUpdates")}
                    className="sr-only peer"
                  />
                  <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>

              <div className="flex items-center justify-between">
                <div>
                  <h3 className="font-medium">Location Tracking</h3>
                  <p className="text-sm text-gray-500">Allow location tracking for location-based services</p>
                </div>
                <label className="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    checked={privacySettings.locationTracking}
                    onChange={() => handlePrivacyToggle("locationTracking")}
                    className="sr-only peer"
                  />
                  <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>

              <button className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Save Privacy Settings
              </button>
            </div>
          )}
        </div>
      </div>

      <div className="bg-white p-6 rounded-lg shadow">
        <h2 className="text-lg font-semibold mb-4">Account Security</h2>
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="font-medium">Two-Factor Authentication</h3>
              <p className="text-sm text-gray-500">Add an extra layer of security to your account</p>
            </div>
            <button className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
              Enable
            </button>
          </div>

          <div className="flex items-center justify-between">
            <div>
              <h3 className="font-medium">Active Sessions</h3>
              <p className="text-sm text-gray-500">Manage your active login sessions</p>
            </div>
            <button className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
              View
            </button>
          </div>

          <div className="flex items-center justify-between">
            <div>
              <h3 className="font-medium">Login History</h3>
              <p className="text-sm text-gray-500">View your recent login activity</p>
            </div>
            <button className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
              View
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}

