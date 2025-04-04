"use client"

import { useEffect, useState } from "react"
import i18n from "i18next"
import { initReactI18next, useTranslation as useTranslationOrg } from "react-i18next"
import resourcesToBackend from "i18next-resources-to-backend"

// Initialize i18next
i18n
  .use(initReactI18next)
  .use(resourcesToBackend((language: string, namespace: string) => import(`./locales/${language}/${namespace}.json`)))
  .init({
    lng: "en",
    fallbackLng: "en",
    ns: ["common"],
    defaultNS: "common",
    interpolation: {
      escapeValue: false,
    },
  })

export function useTranslation() {
  const [mounted, setMounted] = useState(false)
  const ret = useTranslationOrg()

  useEffect(() => {
    // Get language from localStorage
    const storedLang = localStorage.getItem("language")
    if (storedLang && i18n.language !== storedLang) {
      i18n.changeLanguage(storedLang)
    }

    setMounted(true)
  }, [])

  // When rendering client side, we have access to localStorage
  if (mounted) return ret

  // When rendering server side, use default language
  return {
    t: (key: string) => key,
    i18n: {
      language: "en",
      changeLanguage: () => Promise.resolve(),
    },
  }
}

