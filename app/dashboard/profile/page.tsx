"use client"

import type React from "react"

import { useState, useEffect } from "react"
import { useAuth } from "@/lib/auth-context"

// Mock farmer data
const mockFarmerData = {
  verified: true,
  verificationDate: "2023-10-15",
  personalInfo: {
    fullName: "John Doe",
    dateOfBirth: "1985-06-12",
    gender: "Male",
    nationality: "Zambia",
  },
  contactInfo: {
    phone: "+260 97 1234567",
    email: "john.doe@example.com",
    address: {
      street: "Farm Road 123",
      city: "Lusaka",
      region: "Central",
      country: "Zambia",
    },
  },
  farmInfo: {
    farmSize: "5.2 hectares",
    crops: ["Maize", "Soybeans", "Groundnuts"],
    livestock: ["Cattle", "Goats"],
    registrationDate: "2022-03-18",
  },
}

export default function ProfilePage() {
  const { user } = useAuth()
  const [activeTab, setActiveTab] = useState("personal")
  const [farmerData, setFarmerData] = useState(mockFarmerData)
  const [contactInfo, setContactInfo] = useState({
    phone: "",
    email: "",
    address: {
      street: "",
      city: "",
      region: "",
      country: "",
    },
  })
  const [isSaving, setIsSaving] = useState(false)

  useEffect(() => {
    // Set contact info from farmer data
    setContactInfo({
      phone: farmerData.contactInfo.phone,
      email: farmerData.contactInfo.email,
      address: {
        street: farmerData.contactInfo.address.street,
        city: farmerData.contactInfo.address.city,
        region: farmerData.contactInfo.address.region,
        country: farmerData.contactInfo.address.country,
      },
    })
  }, [farmerData])

  const handleContactInfoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target

    if (name.includes(".")) {
      const [parent, child] = name.split(".")
      setContactInfo((prev) => ({
        ...prev,
        [parent]: {
          ...prev[parent as keyof typeof prev],
          [child]: value,
        },
      }))
    } else {
      setContactInfo((prev) => ({
        ...prev,
        [name]: value,
      }))
    }
  }

  const handleSaveChanges = () => {
    setIsSaving(true)

    // Simulate API call
    setTimeout(() => {
      // Update farmer data with new contact info
      setFarmerData((prev) => ({
        ...prev,
        contactInfo: contactInfo,
      }))

      setIsSaving(false)
      alert("Contact information updated successfully")
    }, 1000)
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Farmer Profile</h1>

      <div className="grid gap-6 md:grid-cols-[1fr_300px]">
        <div>
          <div className="bg-white rounded-lg shadow overflow-hidden">
            <div className="border-b border-gray-200">
              <nav className="-mb-px flex">
                <button
                  onClick={() => setActiveTab("personal")}
                  className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                    activeTab === "personal"
                      ? "border-blue-500 text-blue-600"
                      : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                  }`}
                >
                  Personal Information
                </button>
                <button
                  onClick={() => setActiveTab("farm")}
                  className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                    activeTab === "farm"
                      ? "border-blue-500 text-blue-600"
                      : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                  }`}
                >
                  Farm Information
                </button>
                <button
                  onClick={() => setActiveTab("contact")}
                  className={`py-4 px-6 text-center border-b-2 font-medium text-sm ${
                    activeTab === "contact"
                      ? "border-blue-500 text-blue-600"
                      : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                  }`}
                >
                  Contact Information
                </button>
              </nav>
            </div>

            <div className="p-6">
              {activeTab === "personal" && (
                <div className="space-y-4">
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                      <label className="block text-sm font-medium text-gray-500">Full Name</label>
                      <p className="mt-1 font-medium">{farmerData.personalInfo.fullName}</p>
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-500">Farmer ID</label>
                      <p className="mt-1 font-medium">{user?.farmerId}</p>
                    </div>
                  </div>

                  <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                      <label className="block text-sm font-medium text-gray-500">Date of Birth</label>
                      <p className="mt-1 font-medium">{farmerData.personalInfo.dateOfBirth}</p>
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-500">Gender</label>
                      <p className="mt-1 font-medium">{farmerData.personalInfo.gender}</p>
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-500">Nationality</label>
                    <p className="mt-1 font-medium">{farmerData.personalInfo.nationality}</p>
                  </div>

                  <p className="text-sm text-gray-500 border-t pt-4 mt-6">
                    Personal information can only be updated through your local agricultural office.
                  </p>
                </div>
              )}

              {activeTab === "farm" && (
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-500">Farm Size</label>
                    <p className="mt-1 font-medium">{farmerData.farmInfo.farmSize}</p>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-500">Crops</label>
                    <div className="mt-1 flex flex-wrap gap-2">
                      {farmerData.farmInfo.crops.map((crop) => (
                        <span
                          key={crop}
                          className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                        >
                          {crop}
                        </span>
                      ))}
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-500">Livestock</label>
                    <div className="mt-1 flex flex-wrap gap-2">
                      {farmerData.farmInfo.livestock.map((animal) => (
                        <span
                          key={animal}
                          className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                        >
                          {animal}
                        </span>
                      ))}
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-500">Registration Date</label>
                    <p className="mt-1 font-medium">{farmerData.farmInfo.registrationDate}</p>
                  </div>

                  <p className="text-sm text-gray-500 border-t pt-4 mt-6">
                    Farm information can be updated during the annual farm census.
                  </p>
                </div>
              )}

              {activeTab === "contact" && (
                <div className="space-y-4">
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                      <input
                        type="text"
                        name="phone"
                        value={contactInfo.phone}
                        onChange={handleContactInfoChange}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                      <input
                        type="email"
                        name="email"
                        value={contactInfo.email}
                        onChange={handleContactInfoChange}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                    <input
                      type="text"
                      name="address.street"
                      value={contactInfo.address.street}
                      onChange={handleContactInfoChange}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md"
                    />
                  </div>

                  <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">City/Town</label>
                      <input
                        type="text"
                        name="address.city"
                        value={contactInfo.address.city}
                        onChange={handleContactInfoChange}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">Region/Province</label>
                      <input
                        type="text"
                        name="address.region"
                        value={contactInfo.address.region}
                        onChange={handleContactInfoChange}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <input
                      type="text"
                      name="address.country"
                      value={contactInfo.address.country}
                      onChange={handleContactInfoChange}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md"
                    />
                  </div>

                  <div className="flex justify-end gap-2 pt-4 border-t mt-6">
                    <button
                      onClick={() => {
                        setContactInfo({
                          phone: farmerData.contactInfo.phone,
                          email: farmerData.contactInfo.email,
                          address: {
                            street: farmerData.contactInfo.address.street,
                            city: farmerData.contactInfo.address.city,
                            region: farmerData.contactInfo.address.region,
                            country: farmerData.contactInfo.address.country,
                          },
                        })
                      }}
                      className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                      Reset
                    </button>
                    <button
                      onClick={handleSaveChanges}
                      disabled={isSaving}
                      className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                      {isSaving ? "Saving..." : "Save Changes"}
                    </button>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        <div className="space-y-6">
          <div className="bg-white p-6 rounded-lg shadow">
            <h2 className="text-lg font-semibold mb-4">ID Status</h2>
            <div className="flex flex-col items-center justify-center text-center">
              {farmerData.verified ? (
                <>
                  <div className="mb-4 rounded-full bg-green-100 p-3">
                    <svg className="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                  <h3 className="font-medium">Verified</h3>
                  <p className="text-sm text-gray-500 mt-1">Your digital identity has been verified through MOSIP</p>
                  <p className="text-xs text-gray-500 mt-4">Verified on: {farmerData.verificationDate}</p>
                </>
              ) : (
                <>
                  <div className="mb-4 rounded-full bg-yellow-100 p-3">
                    <svg className="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                      />
                    </svg>
                  </div>
                  <h3 className="font-medium">Not Verified</h3>
                  <p className="text-sm text-gray-500 mt-1">Your digital identity needs verification</p>
                  <button className="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Verify Now
                  </button>
                </>
              )}
            </div>
          </div>

          <div className="bg-white p-6 rounded-lg shadow">
            <h2 className="text-lg font-semibold mb-4">Account Activity</h2>
            <div className="space-y-4">
              <div className="border-b pb-2">
                <p className="text-sm font-medium">Profile Updated</p>
                <p className="text-xs text-gray-500">2 days ago</p>
              </div>
              <div className="border-b pb-2">
                <p className="text-sm font-medium">Login via eSignet</p>
                <p className="text-xs text-gray-500">1 week ago</p>
              </div>
              <div>
                <p className="text-sm font-medium">Account Created</p>
                <p className="text-xs text-gray-500">3 months ago</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

