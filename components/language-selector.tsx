"use client"

import { useState, useEffect } from "react"
import { Check, Globe } from "lucide-react"
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu"
import { Button } from "@/components/ui/button"
import { useTranslation } from "@/lib/i18n/client"

const languages = [
  { code: "en", name: "English" },
  { code: "fr", name: "Français" },
  { code: "pt", name: "Português" },
  { code: "sw", name: "Kiswahili" },
]

export function LanguageSelector() {
  const { i18n } = useTranslation()
  const [currentLang, setCurrentLang] = useState(i18n.language || "en")

  useEffect(() => {
    setCurrentLang(i18n.language || "en")
  }, [i18n.language])

  const changeLang = (lang: string) => {
    i18n.changeLanguage(lang)
    localStorage.setItem("language", lang)
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="icon">
          <Globe className="h-5 w-5" />
          <span className="sr-only">Select language</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        {languages.map((lang) => (
          <DropdownMenuItem
            key={lang.code}
            onClick={() => changeLang(lang.code)}
            className="flex items-center justify-between"
          >
            {lang.name}
            {currentLang === lang.code && <Check className="h-4 w-4 ml-2" />}
          </DropdownMenuItem>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}

