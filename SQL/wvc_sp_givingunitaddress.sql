USE [ArenaDB]
GO

/****** Object:  StoredProcedure [dbo].[wvc_sp_givingUnitAddress]    Script Date: 12/30/2012 11:41:49 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		Matt Baylor
-- Create date: 1/24/2011
-- Description:	return best address info for a giving unit
-- =============================================
CREATE PROCEDURE [dbo].[wvc_sp_givingUnitAddress] 
	-- Add the parameters for the stored procedure here
	@GivingUnitID nvarchar(35)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
	SELECT TOP 1 [ArenaDB].[dbo].[wvc_funct_givingGreeting](@GivingUnitID) AS greeting,ca.street_address_1, ca.street_address_2, ca.city, ca.state, ca.postal_code
	FROM core_person cp
		INNER JOIN core_person_address cpa ON cp.person_id = cpa.person_id
		INNER JOIN core_address ca ON cpa.address_id = ca.address_id
		INNER JOIN core_family_member cfm ON cp.person_id = cfm.person_id
	WHERE cpa.primary_address = 1
		AND cfm.role_luid = 29
		AND cp.giving_unit_id = @GivingUnitID
END

GO

