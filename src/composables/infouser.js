import { useRouter } from 'vue-router'
import { Cookies } from 'quasar'

export function getInfoUser() {
  const router = useRouter()

  const infoUser = async () => {
    const token = Cookies.get('token_user')

    if (!token) {
      router.push({ path: '/' })
      return
    }

    try {
      const response = await fetch('http://10.0.52.187:1337/api/users/me?populate=role', {
        headers: {
          Authorization: `Bearer ${token}`
        }
      })

      if (!response.ok) throw new Error('Erreur HTTP ' + response.status)

      const data = await response.json()
      return data

    } catch (error) {
      console.error(error)
    }
  }

  return { infoUser }
}
