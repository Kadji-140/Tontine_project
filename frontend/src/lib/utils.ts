import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Fusionne intelligemment les classes Tailwind.
 */
export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

/**
 * Formate un montant en Francs CFA (ex: 25 000 F).
 */
export function formaterMontant(montant: number | string | undefined | null): string {
  if (montant === undefined || montant === null || isNaN(Number(montant))) {
    return '0 FCFA';
  }
  const valeur = Math.round(Number(montant));
  return new Intl.NumberFormat('fr-FR').format(valeur) + ' FCFA';
}

/**
 * Formate une date au format français standard (ex: 15/01/2026).
 */
export function formaterDate(dateString: string | undefined | null): string {
  if (!dateString) return '-';
  try {
    const d = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    }).format(d);
  } catch {
    return dateString;
  }
}
